## Release Note Generator

This is a command line tool that generates markdown formatted release notes between two branches/tags.

## Installation

Install the package globally via composer:

``` sh
composer global require foodkit/automated-release-notes
```

## Configuration

The following configuration parameters can be passed as argument:

* `--host` issue tracker host (https://project.atlassian.net)
* `--user` issue tracker username
* `--pass` issue tracker password
* `--regex` issue prefix regular expression
* `--format` output format, can be either 'github' or 'slack'

Or, they can be placed in `.env` file within a project:

```
JIRA_USERNAME=user
JIRA_PASSWORD=secret
JIRA_URL=https://ginjath.atlassian.net
JIRA_ISSUE_REGEX=/GT-[\d]+/
GIT_CUSTOM_HOSTS=github.local:github,bitbucket.local:bitbucket
```

The user credential parameters can be omitted if your Jira issue api is public.

## Usage

This command will generate the release notes between two tags.

``` sh
release-notes generate --start=v2.7.8 --end=v2.8.0
```

This will generate the release notes between two branches.

``` sh
release-notes generate --start=develop --end=master
```
