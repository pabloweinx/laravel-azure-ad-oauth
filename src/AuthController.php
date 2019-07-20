<?php

namespace Metrogistics\AzureSocialite;

use Illuminate\Routing\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function redirectToOauthProvider()
    {
        return Socialite::driver('azure-oauth')->redirect();
    }

    public function handleOauthResponse(Request $request)
    {
    	Log::debug('>>handleOauthResponse>> input:\n' . print_r($request->all(), true));
        $user = Socialite::driver('azure-oauth')->user();
        
        Log::debug('Tenemos respuesta de Azure');

        /*
        foreach($user as $var => $value){
        	Log::debug("AzureUser->$var: $value");
        }
        */
        
        //TO-DO Weinx: aquí no debería hacerse la autenticación. Habría que hacer lo que ahora hace la API a la hora de obtener un token y devolverlo a la App.
        Log::debug(print_r($user, true));
        

        $authUser = $this->findOrCreateUser($user);

        auth()->login($authUser, true);

        // session([
        //     'azure_user' => $user
        // ]);

        echo "Autenticado como ". $authUser->email;
        
        /* return redirect(
            config('azure-oath.redirect_on_login')
        ); */
    }

    protected function findOrCreateUser($user)
    {
        $user_class = config('azure-oath.user_class');
        $authUser = $user_class::where(config('azure-oath.user_id_field'), $user->id)->first();

        if ($authUser) {
            return $authUser;
        }

        $UserFactory = new UserFactory();

        return $UserFactory->convertAzureUser($user);
    }
}