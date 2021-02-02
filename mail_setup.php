<?php

#=========   Email setup  directions   =========#

# At first owner need to create Email app for sending Email

#===============================================================================
# CMD--Command for Mail
# All of the API based drivers require the Guzzle HTTP library,
# which may be installed via the Composer package manager:
composer require guzzlehttp/guzzle

#===============================================================================
# Generating Mailables
# it will be generated " app/Mail" for you when you create your first mailable class using the make:mail Artisan command:
php artisan make:mail SendPassword     #Example: "SendPassword"

#===============================================================================
# Controller for sent email.
#Example...
use Mail;
use App\Mail\SentPassword;
public function handleProviderCallback()
{
    $user = Socialite::driver('github')->user();
    $check_email = User::where("email", $user->getEmail())->count();
    if ($check_email != 1) {
        $generated_password = rand(100000, 999990);
        $insert = User::insert([
            "name"=>$user->getNickname(),
            "email"=>$user->getEmail(),
            "role_id"=>5,
            "password"=>Hash::make($generated_password),
            "email_verified_at"=>Carbon::now(),
            "created_at"=>Carbon::now(),
        ]);

        if ($insert) {
            Session::put("socialite_data", [
                "generated_password"=>$generated_password,
                "taken_email"=>$user->getEmail(),
            ]);
            Mail::to($user->getEmail())->send(new SentPassword($generated_password));
            return redirect("login_register");
        } else {
            return back()->withUnsuccess("Register Failed ! Try again !");
        };
    } else {
        Session::put("used_email", [
            "take_used_email"=>$user->getEmail(),
        ]);
        return redirect("login_register")->withUnsuccess("Your Social email already used ! Use your password to login or click bellow for Forget or Change password");
    };
}

#===============================================================================
#Go--> "app/Mail/SentPassword" for take data & sent selected file
#example...
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SentPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $take_password = "";

    public function __construct($generated_password)
    {
        $this->take_password = $generated_password;
    }

    public function build()
    {
        return $this->markdown("admin.mail.send_password");   // for laravel email file systwm
        # OR -->>
        return $this->view("admin.mail.send_password");       // for custimize table file
    }
}

#===============================================================================
# View file in "resources/views/-----myChoice"
# Example... ( This is markdown file )

@component('mail::message')
# Your Password is : <b>{{ $take_password }}</b>

For Login Click here

@component('mail::button', ['url' => "http://127.0.0.1:8000/login_register"])
Continue Login
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent



#===============================================================================
