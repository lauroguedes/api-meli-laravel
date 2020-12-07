## Integration Auth Meli API for Laravel

Example of authentication and [sending notifications](https://developers.mercadolivre.com.br/pt_br/produto-receba-notificacoes#orders) Mercado Livre using Laravel Socialite and the Provider of Mercado Livre.

- Laravel 6.x
- [Laravel Socialite](https://laravel.com/docs/6.x/socialite)
- [Provider Socialite Mercado Livre](https://socialiteproviders.com/MercadoLibre/#installation-basic-usage)

### Routes

- http://yourdomain.com/meli/login
- http://yourdomain.com/meli/callback
- http://yourdomain.com/meli/notifications
### How to test

- Create [your App](https://developers.mercadolivre.com.br/pt_br/registre-o-seu-aplicativo) in Mercado Livre;
- Copy App Id, Secret Key, Redirect URI and paste in env file (MERCADOLIBRE_CLIENT_ID, MERCADOLIBRE_CLIENT_SECRET, MERCADOLIBRE_REDIRECT_URI);
- Create the [test user](https://developers.mercadolivre.com.br/pt_br/registre-o-seu-aplicativo/realizacao-de-testes);
- Authentique using one of the test users. Go in dropdown menu in top right corner and click in **Authenticate in MELI**;
- Use token and refresh token to [consume API](https://developers.mercadolivre.com.br/pt_br/api-docs-pt-br).