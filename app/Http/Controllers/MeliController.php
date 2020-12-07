<?php

namespace App\Http\Controllers;

use App\Integration;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class MeliController extends Controller
{

    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function redirectToProvider()
    {
        return Socialite::driver('mercadolibre')->redirect();
    }

    public function handleProviderCallback()
    {
        $meliUser = Socialite::driver('mercadolibre')->user();
        $user = auth()->user();

        if ($meliUser) {
            $user->integrations()->firstOrCreate([
                'app_id' => config('services.mercadolibre.client_id')
            ],[
                'token' => $meliUser->token,
                'refresh_token' => $meliUser->refreshToken,
                'integration_user_id' => $meliUser->id,
                'app_id' => config('services.mercadolibre.client_id'),
                'integration' => 'mercadolibre',
            ]);
        }

        return view('home');
    }

    public function notifications(Request $request)
    {
        /*
        * 1) Receive Json with the resource that has changed
        * 2) Get the user's token using the id from the request
        * (This id must be recorded somewhere after the user has
        * type done authentication in Meli)
        * 3) Send the request to the resource by placing the token to get more
        * details of the change
        * 4) If the token is expired (will return an error instead of details)
        * a new authentication must be made
        * with the refresh_token that was also recorded when the user made the
        * first authentication
        * 5) Given the details in hand, perform the order creation / change flow
        * 6) See the possibility to update the refresh token so you never need to
        * the user performs authentication manually
        */

        $integration = Integration::with('user')
                                ->where('integration_user_id', $request->user_id)
                                ->where('app_id', $request->application_id)
                                ->first();

        if ($integration) {
            //Chamada a API
            $response = $this->getDetails($request->resource, $integration->token);

            if ($response->getStatusCode() !== 200) {
                if ($response->getStatusCode() === 400) {
                    $updateTokens = $this->client->post('https://api.mercadolibre.com/oauth/token', [
                        'json' => [
                            'grant_type' => 'refresh_token',
                            'client_id' => config('services.mercadolibre.client_id'),
                            'client_secret' => config('services.mercadolibre.client_secret'),
                            'refresh_token' => $integration->refresh_token,
                        ]
                    ]);

                    $data = json_decode($updateTokens->getBody()->getContents());
                    $integration->token = $data->access_token;
                    $integration->refresh_token = $data->refresh_token;
                    $integration->save();

                    $response = $this->getDetails($request->resource, $integration->token);
                } else {
                    return 'Não autorizado';
                }
            }

            $data = json_decode($response->getBody()->getContents());

            return response()->json([
                'data' => $data
            ]);
        }

        return response()->json([
            'data' => 'Não há integração'
        ]);
    }

    private function getDetails($resource, $token)
    {
        try {
            return $this->client->get("https://api.mercadolibre.com$resource?access_token=$token");
        } catch (RequestException $e) {
            if (!$e->hasResponse()) {
                return null;
            }
            return $e->getResponse();
        }
    }
}
