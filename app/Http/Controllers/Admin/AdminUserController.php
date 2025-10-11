<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pack;
use App\Models\Coach;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $q    = trim($request->get('q', ''));
        $char = $request->get('char');

        $users = User::query()
            ->when(true, fn($qq) =>
                // ruolo “user” — adatta se il tuo campo si chiama diversamente
                $qq->where('role', 'user')
            )
            ->when($q !== '', function($qq) use ($q) {
                $qq->where(function($w) use ($q){
                    $w->where('name','like',"%{$q}%")
                      ->orWhere('email','like',"%{$q}%");
                });
            })
            ->when($char, function($qq) use ($char){
                $qq->where('name','like', $char.'%');
            })
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        $letters = range('A','Z');

        return view('admin/users/index', compact('users','q','letters','char'));
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'users_export_'.now()->format('Ymd_His').'.csv';

        $users = User::where('role','user')
            ->orderBy('name')
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->streamDownload(function () use ($users) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 per Excel
            fwrite($out, "\xEF\xBB\xBF");

            // intestazioni CSV (NO avatar/immagine)
            fputcsv($out, [
                'id','name','email','role','is_buyer',
                'created_at','updated_at'
            ]);

            foreach ($users as $u) {
                fputcsv($out, [
                    $u->id,
                    $u->name,
                    $u->email,
                    $u->role,
                    // buyer: se hai un campo diretto ok; altrimenti inferito da acquisti
                    method_exists($u,'hasAnyPurchase') ? ($u->hasAnyPurchase() ? 1 : 0) : null,
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
            'name'   => ['required','string','max:255'],
            'email'  => ['required','email','max:255','unique:users,email'],
            'password' => ['nullable','string','min:8'],
            'is_buyer' => ['nullable','boolean'],
            'pack_ids'   => ['array'],
            'pack_ids.*' => ['integer'],
            'coach_ids'   => ['array'],
            'coach_ids.*' => ['integer'],
        ]);

        // password: se non specificata, random
        $pwd = $data['password'] ?? Str::random(12);

        $user = new User();
        $user->name  = $data['name'];
        $user->email = $data['email'];
        $user->role  = 'user';
        $user->password = bcrypt($pwd);
        $user->save();

        // associazioni acquisti
        $this->syncPurchases($user, $data['pack_ids'] ?? [], $data['coach_ids'] ?? []);

        // opzionale: flag buyer se lo gestisci come campo
        if (array_key_exists('is_buyer', $data) && property_exists($user,'is_buyer')) {
            $user->is_buyer = (bool)$data['is_buyer'];
            $user->save();
        }

        return redirect()->route('admin.users.show', $user)->with('ok','User created');
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
            'name'      => ['required','string','max:255'],
            // email/password/avatar non modificabili
            'is_buyer'  => ['nullable','boolean'],
            'pack_ids'   => ['array'],
            'pack_ids.*' => ['integer'],
            'coach_ids'   => ['array'],
            'coach_ids.*' => ['integer'],
        ]);

        $user->name = $data['name'];

        if (array_key_exists('is_buyer', $data) && property_exists($user,'is_buyer')) {
            $user->is_buyer = (bool)$data['is_buyer'];
        }

        $user->save();

        $this->syncPurchases($user, $data['pack_ids'] ?? [], $data['coach_ids'] ?? []);

        return redirect()->route('admin.users.show',$user)->with('ok','User updated');
    }

    /* ================= Helpers per acquisti ================= */

    protected function syncPurchases(User $user, array $packIds, array $coachIds): void
    {
        // Se esiste un service Purchases con metodi di sync, usalo
        if (class_exists(\App\Support\Purchases::class)) {
            $P = \App\Support\Purchases::class;

            // Se esistono metodi di sync diretti
            if (method_exists($P,'syncUserPacks'))  { $P::syncUserPacks($user->id, $packIds); }
            if (method_exists($P,'syncUserCoaches')){ $P::syncUserCoaches($user->id, $coachIds); }

            // altrimenti prova grant/revoke
            if (method_exists($P,'userHasPack') && method_exists($P,'grantPackToUser') && method_exists($P,'revokePackFromUser')) {
                $current = $this->currentPackIds($user);
                foreach (array_diff($packIds, $current) as $add)   { $P::grantPackToUser($user->id, $add); }
                foreach (array_diff($current, $packIds) as $rem)   { $P::revokePackFromUser($user->id, $rem); }
            }
            if (method_exists($P,'userHasCoach') && method_exists($P,'grantCoachToUser') && method_exists($P,'revokeCoachFromUser')) {
                $current = $this->currentCoachIds($user);
                foreach (array_diff($coachIds, $current) as $add) { $P::grantCoachToUser($user->id, $add); }
                foreach (array_diff($current, $coachIds) as $rem) { $P::revokeCoachFromUser($user->id, $rem); }
            }
            return;
        }

        // Fallback: pivot convenzionali se presenti
        if (method_exists($user, 'packs'))   { $user->packs()->sync($packIds); }
        if (method_exists($user, 'coaches')) { $user->coaches()->sync($coachIds); }
    }

    protected function currentPackIds(User $user): array
    {
        // Se hai relazioni Eloquent
        if (method_exists($user,'packs')) {
            return $user->packs()->pluck('id')->all();
        }
        // Se hai service Purchases
        if (class_exists(\App\Support\Purchases::class) && method_exists(\App\Support\Purchases::class,'userPackIds')) {
            return \App\Support\Purchases::userPackIds($user->id);
        }
        return [];
    }

    protected function currentCoachIds(User $user): array
    {
        if (method_exists($user,'coaches')) {
            return $user->coaches()->pluck('id')->all();
        }
        if (class_exists(\App\Support\Purchases::class) && method_exists(\App\Support\Purchases::class,'userCoachIds')) {
            return \App\Support\Purchases::userCoachIds($user->id);
        }
        return [];
    }
}
