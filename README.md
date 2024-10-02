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

# Notes

Normally there'd be a Makefile to simplify the command running but I'm stuck developing this on windows and it's a bit
of a pain to set up.

Describe any design decisions, security considerations, and trade-offs made during
the implementation.
