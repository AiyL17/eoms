<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function updateInfo(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'position' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'updateInfo')
                ->withInput();
        }

        $user->update($validator->validated());

        return back()->with('success', 'Profile information updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'updatePassword');
        }

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }

    public function updateSignature(Request $request)
    {
        $request->validate([
            'signature_data' => ['nullable', 'string', 'max:200000', 'regex:/^data:image\/png;base64,[A-Za-z0-9+\/]+=*$/'],
        ]);

        $user = Auth::user();
        $path = "signatures/users/{$user->id}.png";

        if ($request->filled('signature_data')) {
            // Decode and persist the PNG file
            $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $request->signature_data);
            $data   = base64_decode($base64);
            if ($data) {
                Storage::disk('local')->put($path, $data);
                $user->update(['signature_path' => $path]);
            }
        } else {
            // Clear: delete the file and null the path
            if ($user->signature_path && Storage::disk('local')->exists($user->signature_path)) {
                Storage::disk('local')->delete($user->signature_path);
            }
            $user->update(['signature_path' => null]);
        }

        return back()->with('success', 'E-signature saved successfully.');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
        ]);

        $user = Auth::user();

        // Delete old avatar file if one exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar' => $path]);

        return back()->with('success', 'Profile picture updated successfully.');
    }

    public function removeAvatar()
    {
        $user = Auth::user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }

        return back()->with('success', 'Profile picture removed.');
    }

    // ─── Serve signature image from local disk ────────────────────────────────

    public function serveSignature(\App\Models\User $user)
    {
        if (! $user->signature_path || ! Storage::disk('local')->exists($user->signature_path)) {
            abort(404);
        }

        return response(Storage::disk('local')->get($user->signature_path), 200, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
