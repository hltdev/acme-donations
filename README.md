## ACME Corp Employee Donations

This github repository serves as a solution for ACME Corp donations platform assignment using:
* **Framework:** Laravel 12
* **Laravel Queues** for handling incoming webhooks from payment gateways
* **Laravel Notifications** to provide donation confirmations to employees
* **Strategy pattern** has been implemented for payment solutions with a `GenericPaymentGateway` placeholder
* **Testing:** written using **Pest**
* **API Documentation:** using **Scramble** (an OpenAPI (Swagger) documentation generator for Laravel)