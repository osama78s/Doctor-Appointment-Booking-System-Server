<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ProfileRequest;
use App\Http\Requests\Users\ForgetPasswordRequest;
use App\Http\Requests\Users\LoginRequest;
use App\Http\Requests\Users\RegisterRequest;
use App\Mail\SendCode;
use App\Models\User;
use App\Traits\ApiTrait;
use App\Traits\Model;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    use ApiTrait, Model;
    
    private function insertData($request)
    {
        $age = Carbon::parse($request->birth_date)->age;
        return
            [
                'first_name'    => $request->first_name,
                'last_name'     => $request->last_name,
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                'age'           => $age,
                'phone'         => $request->phone
            ];
    }

    public function register(RegisterRequest $request)
    {
        $data = $this->insertData($request);
        $user = User::create($data);
        $token = $user->createToken('token')->plainTextToken;
        return $this->data(compact('user', 'token'), '', 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!Hash::check($request->password, $user->password)) {
            return $this->errorsMessage(['error' => 'Email Or Password Is Not Valid']);
        }
        if (is_null($user->email_verified_at)) {
            $token = $user->createToken('token')->plainTextToken;
            return $this->data(compact('user', 'token'), 'You Must Verify Your Email', 403);
        }
        $user->status = 'active';
        $user->save();
        $token = $user->createToken('token')->plainTextToken;
        $user->image_url = asset('images/users/' . $user->image);
        return $this->data(compact('user', 'token'), 'Login Suuccessfully');
    }

    public function logout()
    {
        $user = Auth::user();
        $user->currentAccessToken()->delete();
        $user_db = User::find($user->id);
        $user_db->status = 'un_active';
        $user_db->save();
        return $this->successMessage('Logout Successfully');
    }

    public function forgetPassword(ForgetPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();
        $user->image_url = asset('images/users/' . $user->image);
        $token = $user->createToken('token')->plainTextToken;
        return $this->data(compact('user', 'token'));
    }

    public function profile(ProfileRequest $request)
    {
        $user = User::find(Auth::id());
        $data = $request->except('image');  
        $token = $request->header('Authorization');
        $tokenParts = explode(' ', $token);
        $token = end($tokenParts);

        if($request->hasFile('image')){
            $photoName = $user->image;
            $pathName = public_path('images/users/' . $user->image);
            $this->deletePhotoWithoutDefault($pathName, $photoName);

            $new_image = $this->uploadPhoto($request->image, 'users');
            $data['image'] = $new_image;
        }
        
        $user->update($data);
        $user->image_url = asset('images/users/' . $user->image);
        
        return $this->data(compact('user', 'token'), 'Updated Successfully');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')
        ->with(['prompt' => 'select_account'])
        ->stateless()
        ->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $user = User::where('email', $googleUser->getEmail())
            ->orwhere('google_id', $googleUser->id)->first();

            if (!$user) {
                $user = User::create([
                    'first_name' => explode(' ', $googleUser->getName())[0] ?? 'User',
                    'last_name' => explode(' ', $googleUser->getName())[1] ?? 'Google',
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->id,
                    'password' => Hash::make(Str::random(8)),
                    'email_verified_at' => now(),
                    'image' => 'default.jpg',
                ]);
            }

            $token = $user->createToken('token')->plainTextToken;
            $user->image_url = asset('images/users/' . $user->image);

            $data = [
                'user' => $user,
                'token' => $token
            ];

            return redirect()->to("https://clinic-client-m.vercel.app/auth/google/callback?data=" . urlencode(json_encode($data)));
    }
}
