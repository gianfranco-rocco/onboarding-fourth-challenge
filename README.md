# Onboarding - Fourth Challenge

- [Introduction](#introduction)
- [Built With](#built-with)
- [Getting Started](#getting-started)
  - [Installation/Setup](#installationsetup)
  - [Running](#running)
- [Setting up 3rd-party Services](#setting-up-3rd-party-services)

## Introduction

This projects consists of a website for managing flights reservations.

## Built With

- PHP 8.1.8
- Laravel 9.24.0

## Gesting started

### Installation/Setup

First of all, I highly recommend configuring a shell alias for sail. Follow [these](https://laravel.com/docs/9.x/sail#configuring-a-shell-alias) steps in order to do that and then continue with the steps.

After configuring an alias, follow these steps to setup the development environment:

- cd into the project's root directory
- Run `cp .env.example .env`
- Set the DB database, username and password in `.env`
- Launch Docker Desktop
- Run `{your_sail_alias} up -d`
- Run `{your_sail_alias} composer install`
- Run `{your_sail_alias} artisan key:generate`
- Run `{your_sail_alias} artisan migrate --seed`
- Run `{your_sail_alias} npm install`

### Running

- Launch Docker Desktop
- Run `{your_sail_alias} npm run dev`
- Run `{your_sail_alias} php artisan serve`
- Run `{your_sail_alias} up -d`

## Setting up 3rd-party Services

- [Docker](https://docs.docker.com/get-docker/): Download Docker Desktop to be able to follow both the [Installation/Setup](#installationsetup) and [Running](#running) steps.