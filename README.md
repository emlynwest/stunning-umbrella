# Ticket Tailor Tech Test

This project was created with https://github.com/dunglas/symfony-docker just to get something running quickly. It's a bit
overkill for just a quick tech test really.

## Running

First you'll need to up the docker image.

TODO: Check if this can be simplified
1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up --pull always -d --wait` to set up and start a fresh Symfony project

### Running the command

1. `docker compose exec php bin/console webhook:execute`

### Tests

1. `docker compose exec -e APP_ENV=test php bin/phpunit`

Some warnings about the phpunit configuration will be thrown, these should be fixed but I've not bothered to fix them in
favour of working on the actual solution. They should not be an issue for running tests in this context.

# Notes

Normally there'd be a Makefile to simplify the command running but I'm stuck developing this on windows and it's a bit
of a pain to set up.

Describe any design decisions, security considerations, and trade-offs made during
the implementation.

## Design decisions

 - Functionality is reasonably seperated in to services, allowing functionality to be plugged in to other parts of the 
   system as desired.
 - No validation on the CSV file. Normally this would be something I would include but has been left out for the sake of
   timeboxing the tech test.
 - Webhook dispatcher thin layer to provide an abstraction for running the webhook logic. A better implementation could
   be switched in at a later date without having to change too much code.

## Security considerations

 - No validation of webhook URLs, we could be requesting anything!

## Trade-offs

 - Lack of logging.
 - Hardcoded config.
 - Testing is limited to unit testing only. Given more time I'd implement more extensive Integration and E2E tests.
 - Direct references to `App\Service\Webhook\Dispatcher` should be switched out to reference an interface to allow.
   the logic to be easily switched out at a later date using dependency injection. This would make it much easier to change
   the queuing strategy. (eg, for RabbitMQ, Kafka, etc)
 - There should be a better feedback system in place to be able to examine the status of the processing queue as its being
   worked on. Currently it's a bit of a black box whilst running, which is more ok for short queues but for larger, long
   running queues this could prove to be an issue if something goes wrong and we can't see what's happening.
 - The webhook requesting is hardcoded to GET, it might be useful to make this configurable per-webhook for different types
   of integrations.
