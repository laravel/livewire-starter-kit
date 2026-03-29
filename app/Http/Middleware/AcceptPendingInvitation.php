<?php
namespace App\Http\Middleware;

use App\Models\TeamInvitation;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AcceptPendingInvitation
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() || ! $request->session()->has('pending_invitation_code')) {
            return $next($request);
        }

        $code       = $request->session()->pull('pending_invitation_code');
        $invitation = TeamInvitation::where('code', $code)->first();
        $user       = Auth::user();

        if (! $invitation) {
            return $next($request);
        }

        if ($invitation->isAccepted()) {
            session()->flash('error', __('This invitation has already been accepted.'));
            return $next($request);
        }

        if ($invitation->isExpired()) {
            session()->flash('error', __('This invitation has expired.'));
            return $next($request);
        }

        if (Str::lower($invitation->email) !== Str::lower($user->email)) {
            session()->flash('error', __('The invitation was sent to a different email address.'));
            return $next($request);
        }

        DB::transaction(function () use ($invitation, $user) {
            $team = $invitation->team;

            $team->memberships()->firstOrCreate(
                ['user_id' => $user->id],
                ['role' => $invitation->role]
            );

            $invitation->update(['accepted_at' => now()]);

            $user->switchTeam($team);
        });

        return redirect()->route('dashboard');
    }
}
