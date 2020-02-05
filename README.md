# Slim 4 Boilerplate

#### Hapify

This boilerplate is meant to be used with Hapify. To learn more about Hapify setup, please refer to https://www.hapify.io/get-started.

#### Stack

This boilerplate provides an API built with Slim 4, MySQL and Docker.

## Get Started

### 1. Clone repository

- **Option 1**: Clone and configure this boilerplate using command `hpf new --boilerplate slim_php_tractr`.
- **Option 2**: You can clone this repository and change the project id in file `hapify.json` by running command `hpf use`.

### 2. Generate code

Then you need to generate code from your Hapify project using `hpf generate`.

⚠️ **Important**: For development purpose, generated files are ignored in the `.gitignore`. You should edit this file and remove the last lines before committing.

### 3. Run the API

This API should be used with docker and docker-compose.

#### 3.1 Installation

Run installation scripts to create SQL structure and insert an admin:

**Install dependencies**
```bash
docker-compose run --rm composer
```

**Setup database**
```bash
docker-compose run --rm php php app/cmd/setup/index.php
```

**Insert admin user**
```bash
docker-compose run --rm php php app/cmd/insert-admin/index.php
```

**Or run all in one command**
```bash
docker-compose run --rm composer && docker-compose run --rm php bash -c "php app/cmd/setup/index.php && php app/cmd/insert-admin/index.php"
```

The login and password of the admin user is defined in file `app/cmd/insert-admin/admin.php` (`admin@example.com` / `admin`).

#### 3.2 Start server

To start the API, run this command

```bash
docker-compose up api php
```

Now the API is available on `http://localhost:3000`.

#### 3.3 Insert development data

To insert randomized data into the database, run this command

```bash
docker-compose run --rm php php app/cmd/populate/index.php
```

#### 3.4 Explore data

You can run PhpMyAdmin to browse database. Start the service by running the command bellow and go to `http://localhost:8000`

```bash
docker-compose up phpmyadmin
```

#### 3.5 Use a front-end boilerplate

This boilerplate can be used with those front-end boilerplates:

- [Admin dashboard built with Angular](https://github.com/Tractr/boilerplate-ngx-dashboard)
- [Components library built with Angular](https://github.com/Tractr/boilerplate-ngx-components)

#### 3.6 Updates

If you need to update you data models and re-generate code (using [Hapify](https://www.hapify.io/),
you should run this command `docker-compose run --rm php php app/cmd/setup/index.php` to update the SQL structure.

Please refer to [Hapify Best Practices](https://www.hapify.io/documentation/best-practices) to learn more about Git patches within Hapify context.

## Advanced Integration

This boilerplate includes the following modules

- user sessions (sessions are stored in Redis)
- users accesses management

## Models interpretation

This boilerplate interprets [Hapify](https://www.hapify.io/) data-models fields properties as described bellow:

- **Primary**: Represent the MySQL Id.
- **Unique**: Creates an unique index and throw a 409 in case of conflict.
- **Label**: Allow partial match search for this field.
- **Nullable**: Allow null value to be send for POST and PATCH endpoints. Also define the column as nullable in MySQL.
- **Multiple**: Only used for entity relation for Many-to-Many relation. If also searchable, it performs search using operator `OR`.
- **Embedded**: Only used for entity relation. It joins related entities in search results. Related entities are always joined in a read response.
- **Searchable**: Allows this field in query params for search and count endpoints. If the field is also a `DateTime` or a `Number`, it adds `min` and `max` query params. It also creates an index in MySQL.
- **Sortable**: Allows this field's name as value in the query param `_sort` of the endpoint search. Also create an index for MySQL.
- **Hidden**: Hide this field from API's responses.
- **Internal**: This field is not settable. For the POST endpoint, it guess a suitable value for this field. You may need to edit this default value after code generation.
- **Restricted**: This field is allowed in POST and PATCH endpoints only for admins. An admin is a user with the field `role='admin'`.
- **Ownership**: This field is used to allow the request when the access of the action is made by an `owner`. The value of the field is compared to the connected user id. For search and count endpoints, if also searchable, it forces to perform the lookup in the owner's documents.

## Roadmap

- Add a documentation of the generated API.
- ~~Add a population script that inserts random data in the database.~~
- Ability to migrate data structure when running the setup script.