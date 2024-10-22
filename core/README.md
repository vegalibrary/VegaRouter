# PHP Router Library Documentation

A lightweight and flexible PHP router with support for layouts, middleware, and static file serving.

## Table of Contents

-   [Installation](#installation)
-   [Project Structure](#project-structure)
-   [Basic Usage](#basic-usage)
-   [Features](#features)
    -   [Routing](#routing)
    -   [Route Groups](#route-groups)
    -   [Layouts](#layouts)
    -   [Middleware](#middleware)
    -   [Static Files](#static-files)
    -   [Views](#views)
    -   [Components](#components)
-   [API Reference](#api-reference)
-   [Examples](#examples)
-   [Configuration](#configuration)

## Installation

1. Copy the `Router.php` file to your project.
2. Include it in your entry point file:

```php
require_once 'path/to/Router.php';
```

## Project Structure

A well-organized project structure is crucial for maintaining clarity and scalability. Below is an example folder structure for a typical project using the **PHP Router Library**:

```
/your-project
├── app
│   ├── components          # Reusable components (e.g., header, footer)
│   │   ├── header.php
│   │   └── footer.php
│   ├── layouts             # Layout templates
│   │   ├── default.php
│   │   └── admin.php
│   ├── views               # Individual views
│   │   ├── auth
│   │   │   └── login.php
│   │   ├── admin
│   │   │   └── dashboard.php
│   │   └── page.php
│   └── Router.php          # The router library itself
├── public                  # Publicly accessible files
│   ├── index.php           # Entry point of the application
│   ├── .htaccess           # Configuration file for URL rewriting
│   └── css                 # Stylesheets
│       └── style.css
└── vendor                  # Composer dependencies (if any)
```

### Explanation of Project Structure

-   **app/components**: Contains reusable components like headers and footers to be included in views.
-   **app/layouts**: Holds layout templates, allowing for consistent structure across pages.
-   **app/views**: Contains the view files for different parts of your application.
-   **public**: The web-accessible directory where the entry point (`index.php`) resides, along with static assets like stylesheets and images.
-   **vendor**: Directory for third-party libraries managed by Composer (if applicable).

## Basic Usage

```php
$router = new Router();

// Define routes
$router->get('/', function() {
    echo "Home page";
});

$router->post('/submit', function() {
    echo "Form submitted";
});

// Dispatch router
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
```

## Features

### Routing

Support for GET and POST routes with parameter extraction:

```php
// Simple route
$router->get('/about', function() {
    echo "About page";
});

// Route with parameters
$router->get('/users/{id}', function($id) {
    echo "User: " . $id;
});
```

### Route Groups

Group related routes with a common prefix:

```php
$router->group('/admin', function($router) {
    $router->get('/dashboard', function() {
        echo "Admin dashboard";
    });

    $router->get('/users', function() {
        echo "Manage users";
    });
});
```

### Layouts

Support for layout templates with group-specific layouts:

```php
// Set default layout
$router->layout('default');

// Set group-specific layout
$router->group('/admin', function($router) {
    $router->layout('admin');

    $router->get('/dashboard', function() use ($router) {
        $router->render('admin/dashboard');
    });
});
```

### Middleware

Add middleware for authentication, logging, etc.:

```php
$router->use(function($next) {
    // Before middleware
    session_start();

    $next();

    // After middleware
    session_write_close();
});

// Authentication middleware
$authMiddleware = function($next) {
    if (isset($_SESSION['user'])) {
        $next();
    } else {
        header('Location: /login');
    }
};

$router->use($authMiddleware);
```

### Static Files

Serve static files from a public directory:

```php
// Set static files folder (default is 'public')
$router->serverStatic('public');
```

### Views

Render views with layouts and variables:

```php
$router->get('/page', function() use ($router) {
    $router->render('page', [
        'title' => 'My Page',
        'content' => 'Welcome to my page!'
    ]);
});
```

### Components

The **Lightweight Router** allows you to include reusable components in your views. This is particularly useful for elements that are shared across multiple views, such as headers, footers, or navigation menus.

#### Steps to Use Components

1. **Create a Components Folder**: Organize your reusable components in the `app/components` directory. For example:

    ```
    /your-project
    ├── app
    │   └── components
    │       ├── header.php
    │       └── footer.php
    ```

2. **Define Your Component**: In `header.php`, you might have something like this:

    ```php
    <header>
        <h1>My Website</h1>
        <nav>
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/about">About</a></li>
                <li><a href="/contact">Contact</a></li>
            </ul>
        </nav>
    </header>
    ```

3. **Include the Component in Your Views**: Use the `component` method in your route definitions to include your components. For example:

    ```php
    $router->get('/page', function() use ($router) {
        $router->render('page', [
            'title' => 'My Page',
            'content' => 'Welcome to my page!'
        ]);
    });

    // In the view file (app/views/page.php)
    <?= $component('header'); ?>
    <div class="page">
        <h1><?= $title ?></h1>
        <div class="content">
            <?= $content ?>
        </div>
    </div>
    <?= $component('footer'); ?>
    ```

By using components, you can avoid duplication and keep your code DRY (Don't Repeat Yourself).

## API Reference

### Main Methods

-   `get($path, $callback)`: Register GET route.
-   `post($path, $callback)`: Register POST route.
-   `group($prefix, $callback)`: Create route group.
-   `use($middleware)`: Add middleware.
-   `layout($layout)`: Set layout.
-   `render($view, $options)`: Render view.
-   `serverStatic($folder)`: Set static files folder.
-   `redirect($url, $statusCode)`: Redirect to URL.
-   `setNotFound($callback)`: Set 404 handler.
-   `component($name)`: Include a component from the components folder.

## Examples

### Complete Application Example

```php
$router = new Router();

// Set default layout
$router->layout('default');

// Add authentication middleware
$router->use(function($next) {
    session_start();
    if (!isset($_SESSION['user']) && $_SERVER['REQUEST_URI'] !== '/login') {
        header('Location: /login');
        return;
    }
    $next();
});

// Public routes
$router->get('/login', function() use ($router) {
    $router->render('auth/login', ['layout' => false]);
});

// Admin routes
$router->group('/admin', function($router) {
    $router->layout('admin');

    $router->get('/dashboard', function() use ($router) {
        $router->render('admin/dashboard', [
            'title' => 'Dashboard',
            'stats' => [/* ... */]
        ]);
    });
});

// Start the router
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
```

### Layout File Example (app/layouts/default.php)

```php
<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?? 'My Site' ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?= $component('header'); ?> <!-- Include header component -->

    <main>
        <?= $content ?>
    </main>

    <?= $component('footer'); ?> <!-- Include footer component -->
</body>
</html>
```

### View File Example (app/views/page.php)

```php
<div class="page">
    <h1><?= $title ?></h1>
    <div class="content">
        <?= $content ?>
    </div>
</div>
```

## Configuration

### .htaccess File

To enable URL rewriting and make your application more user-friendly, create an `.htaccess` file in the `public` directory. This file allows you to remove the `index.php` from the URL and route all requests through the `index.php` file.

Here’s an example of

what your `.htaccess` file should look like:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Remove index.php from URL
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

### Explanation of .htaccess Directives

-   **RewriteEngine On**: Enables the rewriting engine.
-   **RewriteCond %{REQUEST_FILENAME} !-f**: Checks if the requested filename is not an existing file.
-   **RewriteCond %{REQUEST_FILENAME} !-d**: Checks if the requested filename is not an existing directory.
-   **RewriteRule ^(.\*)$ index.php [QSA,L]**: Redirects all requests that are not files or directories to `index.php`, preserving query strings.

This setup allows you to use clean URLs like `/about` instead of `/index.php/about`, enhancing the usability and SEO of your application.
