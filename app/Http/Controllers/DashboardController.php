<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Group;
use App\Models\Zona;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->canAccessDashboard()) {
            return redirect()->route($user->getDefaultHomeRoute());
        }
        
        $data = [
            'usersCount' => 0,
            'rolesCount' => 0,
            'groupsCount' => 0,
            'zonasCount' => 0,
            'myStudentsCount' => 0,
            'myGroups' => collect(),
        ];

        if ($user->hasRole('admin')) {
            $data['usersCount'] = User::count();
            $data['rolesCount'] = Role::count();
            $data['groupsCount'] = Group::count();
            $data['zonasCount'] = Zona::count();
        }

        if ($user->hasRole('profesor')) {
            $data['myGroups'] = Group::where('tutor_id', $user->id)->withCount('students')->get();
            $data['myStudentsCount'] = $user->tutoredGroups()->withCount('students')->get()->sum('students_count');
        }

        return view('dashboard', $data);
    }
}
