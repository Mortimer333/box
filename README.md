# ifxpayments

Mock App for payments

# Setup via Docker
```shell
docker compose up
# or
make start
```
then to set up database:
```shell
php bin/console d:m:m
```
and add `ifxpayments.localhost` to you hosts file:
```shell
127.0.0.1    ifxpayments.localhost
```

# Environment
We are using Symfony 7 + MySQL + Redis + RabbitMQ + Nginx + PHP 8.3


### API
To access REST API docs:
`http://ifxpayments.localhost/api/doc` 
> You will only see routes which name starts with `api_` and path with `/_`


### RabbitMQ
http://ifxpayments.localhost:15672

user: guest

pass: guest

### Supervisor
http://ifxpayments.localhost:9001/

user: admin

pass: admin

# Architecture
For this project I've used DDD with Ports & Adapters to make this framework-agnostic application (as specified in a task).

# Tests
For tests, I'm using [Codeception](https://codeception.com/docs/Introduction) library.

### Run all tests
```shell
make test-all 
```
### Run specific test
```shell
# To run whole file
make single-test FileNameCest
# To run single test
make single-test FileNameCest functionTest
```

I'm focusing mainly on unit tests, so integration tests are pretty scarce.
