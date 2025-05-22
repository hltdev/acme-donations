## ACME Corp Employee Donations

This github repository serves as a solution for ACME Corp donations platform assignment using:
* **Framework:** Laravel 12
* **Laravel Queues** for handling incoming webhooks from payment gateways
* **Laravel Notifications** to provide donation confirmations to employees
* **Strategy pattern** has been implemented for payment solutions with a `GenericPaymentGateway` placeholder
* **Testing:** written using **Pest**
* **Containerization:** with a minimalistic **Docker** configuration 
* **API Documentation:** using **Scramble** (an OpenAPI (Swagger) documentation generator for Laravel)

## Getting Started

1.  `git clone git@github.com:hltdev/acme-donations.git`
2.  `cd acme-donations`
3.  `composer install`
4.  `cp .env.example .env`
5.  `php artisan key:generate`
6.  `php artisan migrate --seed`
7.  `php artisan serve` OR `./vendor/bin/sail up`