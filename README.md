# jwkstool

jwkstool is a command-line tool to manage JSON web key sets
as specified in [RFC7517](http://tools.ietf.org/html/rfc7517).
jwkstool uses [SimpleJWT] for most of its operations.

[![Latest Stable Version](https://poser.pugx.org/kelvinmo/jwkstool/v/stable)](https://packagist.org/packages/kelvinmo/jwkstool)
[![CI](https://github.com/kelvinmo/jwkstool/workflows/CI/badge.svg)](https://github.com/kelvinmo/jwkstool/actions?query=workflow%3ACI)

## Requirements

See the [SimpleJWT] website for system requirements.

## Installation

You can install via [Composer](http://getcomposer.org/).

```sh
composer require kelvinmo/jwkstool
```

A phar file is also available on the [GitHub releases page](https://github.com/kelvinmo/jwkstool/releases)
for released versions.

## Usage

Run `jwkstool list-commands` for a list of commands.  Run
`jwkstool help COMMAND` for more help on each command.

## Licence

BSD 3 clause

[SimpleJWT]: https://github.com/kelvinmo/simplejwt