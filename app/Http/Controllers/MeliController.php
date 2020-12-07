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

    public function meliNotify(Request $request)
    {
        /*
        * 1) Recebe o Json com o recurso que sofreu alteração
        * 2) Pega o token do usuário através do id vindo da requisição
        * (Esse id deverá ser gravado em algum lugar após o usuário ter
        * tipo feito a autenticação no Meli)
        * 3) Manda a requisição para o recurso colocando o token para obter mais
        * detalhes da alteração
        * 4) Caso o token esteja expirado (irá retornar um erro em vez dos detalhes)
        * deverá ser feito uma nova autenticação
        * com o refresh_token que fora gravado também quando o usuário fez a
        * primeira autenticação
        * 5) Diante dos detalhes em mãos realizar o fluxo de criação/alteração de pedidos
        * 6) Ver a possibilidade de atualizar o refresh token para que nunca precise
        * o usuário realizar a autenticação manualmente
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
