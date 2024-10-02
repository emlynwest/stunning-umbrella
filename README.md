# Ticket Tailor Tech Test

This project was created with https://github.com/dunglas/symfony-docker just to get something running quickly. It's a bit
overkill for just a quick tech test really but I wanted to get started quickly and not have to faff around with manually
putting a docker environment together.

One thing I noticed as I was finishing up is that I forgot to init a fresh git repo, so my commits are on top of all the
commits from the original repo. This was not my intention but changing/fixing it would be annoying.

## Running

First you'll need to build and up the docker image.

1. Run `docker compose build --no-cache` to build fresh images.
2. Run `docker compose up` to bring everything up.

### Running the command

To run the main entry point use the following command:

1. `docker compose exec php bin/console webhook:execute -vvv`

### Tests

Tests can easily be run through phpunit:

1. `docker compose exec -e APP_ENV=test php bin/phpunit`

Some warnings about the phpunit configuration will be thrown, these really should be fixed but I've not bothered to fix 
them in favour of working on the actual solution. They should not be an issue for running tests in this context.

# Notes

Normally there'd be a Makefile to simplify the command running but I'm stuck developing this on windows and it's a bit
of a pain to set up.

Quick tour of points of interest:

 - `data/webhooks.txt` - Contains our list of webhooks to process.
 - `src/Command/Webhook/Execute.php` - Our main entry point for the script.
 - `src/Service/Webhook/` - This is where the fun happens and contains the main logic.
   - `Dispatcher.php` - A super simple layer to take a number of webhooks and process them in a worker.
   - `Reader.php` - Responsible for reading webhooks from a csv file and producing a number of `Webhook` models.
   - `Worker.php` - Responsible for making the actual HTTP requests and dealing with the backoff logic.
 - `tests` - Should not need much explanation :D 
   - `tests/data` - Separate test data just to guarantee the tests are as isolated as possible.

## Design decisions

 - Functionality is reasonably seperated in to services, allowing functionality to be plugged in to other parts of the 
   system as desired.
 - No validation on the CSV file. Normally this would be something I would include but has been left out for the sake of
   timeboxing the tech test.
 - Webhook dispatcher is just a thin layer to provide an abstraction for running the webhook logic. 
   A better implementation could be switched in at a later date without having to change too much code.

## Security considerations

 - No validation of webhook URLs, we could be requesting anything! Ideally we'd likely want something like a list of
   allowed domains or even specific endpoints to really lock it down.

## Trade-offs

 - Hardcoded config - Ideally config (such as the webhook location) would not be hardcoded and either provided via
   config or as a parameter to the script.
 - Testing is limited to unit testing only. Given more time I'd implement more extensive Integration and E2E tests.
 - Direct references to `App\Service\Webhook\Dispatcher` should be switched out to reference an interface to allow.
   the logic to be easily switched out at a later date using dependency injection. This would make it much easier to change
   the queuing strategy. (eg, for RabbitMQ, Kafka, etc). The same could be done with the `Worker` class as well.
 - The webhook requesting is hardcoded to GET, it might be useful to make this configurable per-webhook for different types
   of integrations.
 - The current system is single threaded and so not the most performant. A nasty hacky solution could be to use PHP's
   multi-threading or better yet making use of a messaging system such as RabbitMQ or Kafka.
 - When checking the number of endpoint failures there's currently no consideration to query params so `a.com` and 
   `a.com?foo=1` will be treated as totally separate endpoints.
