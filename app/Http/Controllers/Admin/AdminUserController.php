<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pack;
use App\Models\Coach;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $q      = trim($request->get('q', ''));
        $char   = $request->get('char');
        $buyers = $request->boolean('buyers'); // ?buyers=1 -> solo acquirenti

        $base = User::query()
            ->where('role', 'user')
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->when($char, fn ($qq) => $qq->where('name', 'like', $char.'%'));

        // ========= filtro buyers (scalar OR json) =========
        if ($buyers) {
            $base->whereHas('orders', function ($oq) {
                $oq->where('status', 'paid')
                   ->where(function ($w) {
                       $w->whereNotNull('pack_id')
                         ->orWhereNotNull('coach_id')
                         ->orWhereRaw('JSON_LENGTH(pack_id_json) > 0')
                         ->orWhereRaw('JSON_LENGTH(coach_id_json) > 0');
                   });
            });
        }

        $users = $base->orderBy('name')->paginate(30)->withQueryString();

        // ====== Aggrega ordini per gli utenti in pagina (scalar + json) ======
        $userIds = $users->pluck('id')->all();

        $orders = Order::query()
            ->whereIn('user_id', $userIds)
            ->where('status', 'paid')
            ->where(function ($w) {
                $w->whereNotNull('pack_id')
                  ->orWhereNotNull('coach_id')
                  ->orWhereRaw('JSON_LENGTH(pack_id_json) > 0')
                  ->orWhereRaw('JSON_LENGTH(coach_id_json) > 0');
            })
            ->get(['user_id','pack_id','coach_id','pack_id_json','coach_id_json']);

        $packIdsByUser  = [];
        $coachIdsByUser = [];

        $collectIds = function ($scalar, $json): array {
            $out = [];
            if (!is_null($scalar)) { $out[] = (int) $scalar; }
            // grazie ai cast del Model potremmo già avere array
            if (is_array($json)) {
                foreach ($json as $v) { $out[] = (int) $v; }
            } elseif (is_string($json) && $json !== '') {
                $dec = json_decode($json, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($dec)) {
                    foreach ($dec as $v) { $out[] = (int) $v; }
                }
            }
            $out = array_values(array_unique(array_filter($out)));
            return $out;
        };

        foreach ($orders as $o) {
            $uid = (int) $o->user_id;

            $pids = $collectIds($o->pack_id,  $o->pack_id_json);
            $cids = $collectIds($o->coach_id, $o->coach_id_json);

            if ($pids) {
                $packIdsByUser[$uid] = array_values(array_unique(array_merge($packIdsByUser[$uid] ?? [], $pids)));
            }
            if ($cids) {
                $coachIdsByUser[$uid] = array_values(array_unique(array_merge($coachIdsByUser[$uid] ?? [], $cids)));
            }
        }

        $allPackIds  = $packIdsByUser  ? array_values(array_unique(array_merge(...array_values($packIdsByUser))))   : [];
        $allCoachIds = $coachIdsByUser ? array_values(array_unique(array_merge(...array_values($coachIdsByUser)))) : [];

        $packsMap = $allPackIds
            ? Pack::whereIn('id', $allPackIds)->pluck('title', 'id')->all()
            : [];
        $coachMap = $allCoachIds
            ? Coach::whereIn('id', $allCoachIds)->pluck('name', 'id')->all()
            : [];

        $letters = range('A', 'Z');

        return view('admin/users/index', [
            'users'          => $users,
            'q'              => $q,
            'letters'        => $letters,
            'char'           => $char,
            'buyers'         => $buyers,
            'packIdsByUser'  => $packIdsByUser,
            'coachIdsByUser' => $coachIdsByUser,
            'packsMap'       => $packsMap,
            'coachMap'       => $coachMap,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'users_export_'.now()->format('Ymd_His').'.csv';

        $users = User::where('role', 'user')->orderBy('name')->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->streamDownload(function () use ($users) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM

            fputcsv($out, ['id','name','email','role','is_buyer','created_at','updated_at']);

            foreach ($users as $u) {
                // buyer se ha almeno un ordine paid con scalar o json non vuoti
                $isBuyer = Order::where('user_id', $u->id)
                    ->where('status', 'paid')
                    ->where(function ($w) {
                        $w->whereNotNull('pack_id')
                          ->orWhereNotNull('coach_id')
                          ->orWhereRaw('JSON_LENGTH(pack_id_json) > 0')
                          ->orWhereRaw('JSON_LENGTH(coach_id_json) > 0');
                    })
                    ->exists() ? 1 : 0;

                fputcsv($out, [
                    $u->id,
                    $u->name,
                    $u->email,
                    $u->role,
                    $isBuyer,
                    $u->created_at,
                    $u->updated_at,
                ]);
            }

            fclose($out);
        }, $filename, $headers);
    }

    public function create()
    {
        $packs   = Pack::orderBy('title')->get(['id','title']);
        $coaches = Coach::orderBy('name')->get(['id','name']);
        return view('admin/users/create', compact('packs','coaches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required','string','max:255'],
            'email'        => ['required','email','max:255','unique:users,email'],
            'password'     => ['nullable','string','min:8'],
            'is_buyer'     => ['nullable','boolean'],
            'pack_ids'     => ['array'],
            'pack_ids.*'   => ['integer'],
            'coach_ids'    => ['array'],
            'coach_ids.*'  => ['integer'],
        ]);

        $pwd = $data['password'] ?? Str::random(12);

        $user = new User();
        $user->name     = $data['name'];
        $user->email    = $data['email'];
        $user->role     = 'user';
        $user->password = bcrypt($pwd);
        $user->save();

        if (array_key_exists('is_buyer', $data) && property_exists($user, 'is_buyer')) {
            $user->is_buyer = (bool) $data['is_buyer'];
            $user->save();
        }

        $this->syncPurchases($user, $data['pack_ids'] ?? [], $data['coach_ids'] ?? []);

        return redirect()->route('admin.users.show', $user)->with('ok', 'User created');
    }

    public function show(User $user)
    {
        $packs   = Pack::orderBy('title')->get(['id','title']);
        $coaches = Coach::orderBy('name')->get(['id','name']);

        $ownedPacks   = $this->currentPackIds($user);
        $ownedCoaches = $this->currentCoachIds($user);

        return view('admin/users/show', compact('user','packs','coaches','ownedPacks','ownedCoaches'));
    }

    public function edit(User $user)
    {
        $packs   = Pack::orderBy('title')->get(['id','title']);
        $coaches = Coach::orderBy('name')->get(['id','name']);

        $ownedPacks   = $this->currentPackIds($user);
        $ownedCoaches = $this->currentCoachIds($user);

        return view('admin/users/edit', compact('user','packs','coaches','ownedPacks','ownedCoaches'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:255'],
            'is_buyer'    => ['nullable','boolean'],
            'pack_ids'    => ['array'],
            'pack_ids.*'  => ['integer'],
            'coach_ids'   => ['array'],
            'coach_ids.*' => ['integer'],
        ]);

        $user->name = $data['name'];

        if (array_key_exists('is_buyer', $data) && property_exists($user, 'is_buyer')) {
            $user->is_buyer = (bool) $data['is_buyer'];
        }

        $user->save();

        $this->syncPurchases($user, $data['pack_ids'] ?? [], $data['coach_ids'] ?? []);

        return redirect()->route('admin.users.show', $user)->with('ok', 'User updated');
    }

    /* ================= Helpers ================= */

    protected function syncPurchases(User $user, array $packIds, array $coachIds): void
    {
        // Hook opzionali: se hai un service interno o pivot, lasciali attivi
        if (class_exists(\App\Support\Purchases::class)) {
            $P = \App\Support\Purchases::class;
            if (method_exists($P,'syncUserPacks'))   { $P::syncUserPacks($user->id, $packIds); }
            if (method_exists($P,'syncUserCoaches')) { $P::syncUserCoaches($user->id, $coachIds); }
            return;
        }
        if (method_exists($user, 'packs'))   { $user->packs()->sync($packIds); }
        if (method_exists($user, 'coaches')) { $user->coaches()->sync($coachIds); }
    }

    /** Pack acquistati: unione scalar + json dagli ordini PAID */
    protected function currentPackIds(User $user): array
    {
        $orders = Order::where('user_id', $user->id)
            ->where('status','paid')
            ->get(['pack_id','pack_id_json']);

        $out = [];
        foreach ($orders as $o) {
            if (!is_null($o->pack_id)) $out[] = (int) $o->pack_id;
            $json = $o->pack_id_json;
            if (is_array($json)) {
                foreach ($json as $v) $out[] = (int) $v;
            } elseif (is_string($json) && $json !== '') {
                $dec = json_decode($json, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($dec)) {
                    foreach ($dec as $v) $out[] = (int) $v;
                }
            }
        }
        return array_values(array_unique(array_filter($out)));
    }

    /** Coach acquistati: unione scalar + json dagli ordini PAID */
    protected function currentCoachIds(User $user): array
    {
        $orders = Order::where('user_id', $user->id)
            ->where('status','paid')
            ->get(['coach_id','coach_id_json']);

        $out = [];
        foreach ($orders as $o) {
            if (!is_null($o->coach_id)) $out[] = (int) $o->coach_id;
            $json = $o->coach_id_json;
            if (is_array($json)) {
                foreach ($json as $v) $out[] = (int) $v;
            } elseif (is_string($json) && $json !== '') {
                $dec = json_decode($json, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($dec)) {
                    foreach ($dec as $v) $out[] = (int) $v;
                }
            }
        }
        return array_values(array_unique(array_filter($out)));
    }
}