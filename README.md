<p align="center">
  <img src="./logo.png" alt="Logo" width="100" height="auto">
</p>

**S-PHP** is a lightweight, simple MVC (Model-View-Controller) PHP framework designed to help developers build scalable and maintainable web applications. It's perfect for those who need a quick start without the complexity of heavier frameworks.

## Features

- **Lightweight**: Minimalistic design with only the core MVC features.
- **Easy Setup**: No complex configurations; simple to get started.
- **Flexible Routing**: Handles URL-to-controller mappings effortlessly.
- **MVC Architecture**: Organizes code into Models, Views, and Controllers.
- **Extensible**: Easily extendable with additional features.

## Requirements

- PHP 7.4 or higher
- Web server (Apache, Nginx, etc.)
- Composer (optional, for dependency management)

## Installation

### 1. Clone the Repository

Clone this repository to your local machine using Git:

```bash
git clone https://github.com/PranabZz/S-PHP.git
```

### 2. Install Dependencies (Optional)

If you wish to use Composer to manage dependencies, run the following:

```bash
composer install
```
### Project Structure

```bash
S-PHP/
│
├── app/
│   ├── Controllers/
│   ├── Models/
│   └── Views/
│
├── public/
│   └── index.php       # Entry point for the application
│
├── routes/
│   └── web.php         # Defines routes for the application
│
└── .gitignore
```

### Database

The database configuration is done in the `.env` file. You can use SQLite, MySQL, or PostgreSQL.

**SQLite**

To use SQLite, set the `DB_CONNECTION` to `sqlite` in your `.env` file. The `DB_DATABASE` should be the path to your database file.

```
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/your/database.sqlite
```

**MySQL**

To use MySQL, set the `DB_CONNECTION` to `mysql` in your `.env` file. You will also need to set the `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`.

```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=sphp
DB_USERNAME=sphpuser
DB_PASSWORD=dbpassword
```

When you set `DB_CONNECTION` to `mysql`, the `docker-compose.yaml` file will start the `mysql` service.

**PostgreSQL**

To use PostgreSQL, set the `DB_CONNECTION` to `pgsql` in your `.env` file. You will also need to set the `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`.

```
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=sphp
DB_USERNAME=sphpuser
DB_PASSWORD=dbpassword
```

When you set `DB_CONNECTION` to `pgsql`, the `docker-compose.yaml` file will start the `postgres` service.

### Usage

Create a New Controller

To create a new controller, simply create a new file in the app/Controllers directory:

```php
<?php

namespace App\Controllers;

class HomeController
{
    public function index()
    {
        // This method will be invoked when the user accesses the home route
        echo "Welcome to S-PHP!";
    }
}

```


### Define Routes

Routes are defined in routes/web.php. Here is an example route:

```php

$router = new Router();
$router->get('/home', HomeController::class, 'index', Middleware::class);  

```

### Working with Views

You can return views from a controller like this:

```php
namespace App\Controllers;

use Sphp\Core\View;

class HomeController
{
    public function index()
    {
        // Renders the view
        return View::render('home.index');
    }
}
```


## Contributions
If you would like to contribute to this project, feel free to fork the repository and create a pull request with your changes. Please make sure to follow the coding standards and write tests for any new features.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.