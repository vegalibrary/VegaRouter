<?php

/**
 * Router Class
 * 
 * A lightweight PHP router that handles routing, middleware, layouts, and static file serving.
 * Features include:
 * - GET and POST route handling
 * - Route grouping with prefixes
 * - Layout management with group-specific layouts
 * - Middleware support
 * - Static file serving
 * - View rendering with layout support
 * 
 * @package Router
 * @author Original Author
 * @version 1.0.0
 */
class Router {
    /** @var array Stores registered routes */
    protected $routes = [];
    
    /** @var callable Callback for 404 handling */
    protected $notFoundCallback;
    
    /** @var array List of middleware to be executed */
    protected $middleware = [];
    
    /** @var array Route parameters extracted from URL */
    protected $params = [];
    
    /** @var string|null Default layout for views */
    protected $layout = null;
    
    /** @var string Folder for serving static files */
    protected $staticFolder = 'public';
    
    /** @var string Current group prefix */
    protected $currentGroupPrefix = '';
    
    /** @var array Group-specific layouts */
    protected $groupLayouts = [];
    
    /** @var string|null Current group's layout */
    protected $currentGroupLayout = null;

    /**
     * Register a GET route
     * 
     * @param string $path The URL path to match
     * @param callable $callback Function to execute when route is matched
     * @return void
     */
    public function get($path, $callback) {
        $this->addRoute('GET', $path, $callback);
    }

    /**
     * Register a POST route
     * 
     * @param string $path The URL path to match
     * @param callable $callback Function to execute when route is matched
     * @return void
     */
    public function post($path, $callback) {
        $this->addRoute('POST', $path, $callback);
    }

    /**
     * Add a route to the router
     * 
     * @param string $method HTTP method (GET/POST)
     * @param string $path Route path
     * @param callable $callback Route handler
     * @return void
     */
    private function addRoute($method, $path, $callback) {
        $fullPath = $this->trim($this->currentGroupPrefix . $path);
        $this->routes[$method][] = [
            'path' => $fullPath,
            'callback' => $callback
        ];
    }

    /**
     * Create a route group with a prefix
     * 
     * @param string $prefix The prefix for all routes in the group
     * @param callable $callback Function to define routes within the group
     * @return void
     */
    public function group($prefix, $callback) {
        $previousGroupPrefix = $this->currentGroupPrefix;
        $previousGroupLayout = $this->currentGroupLayout;
        
        $this->currentGroupPrefix .= $this->trim($prefix);
        $this->currentGroupLayout = null;
        
        $callback($this);
        
        $this->currentGroupPrefix = $previousGroupPrefix;
        $this->currentGroupLayout = $previousGroupLayout;
    }

    /**
     * Add middleware to the router
     * 
     * @param callable $middleware Middleware function
     * @return void
     */
    public function use($middleware) {
        $this->middleware[] = $middleware;
    }

    /**
     * Set the layout for views
     * 
     * @param string $layout Layout name
     * @return void
     */
    public function layout($layout) {
        if ($this->currentGroupPrefix !== '') {
            $this->groupLayouts[$this->currentGroupPrefix] = $layout;
            $this->currentGroupLayout = $layout;
        } else {
            $this->layout = $layout;
        }
    }

    /**
     * Set the static files folder
     * 
     * @param string $folder Folder path
     * @return void
     */
    public function serverStatic($folder) {
        $this->staticFolder = $folder;
    }

    /**
     * Serve static files from the static folder
     * 
     * @return void
     */
    public function serveStaticFolder() {
        $requestedUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $filePath = __DIR__ . '/../' . $this->staticFolder . ltrim($requestedUri, '/');

        if (file_exists($filePath) && is_file($filePath)) {
            $mimeType = mime_content_type($filePath);
            header('Content-Type: ' . $mimeType);
            readfile($filePath);
            exit;
        }
    }

    /**
     * Dispatch the router
     * 
     * @param string $method HTTP method
     * @param string $path Request path
     * @return void
     */
    public function dispatch($method, $path) {
        $this->serveStaticFolder();

        $trimmedPath = $this->trim($path);
        $routeFound = false;

        if (!isset($this->routes[$method])) {
            $this->handleNotFound();
            return;
        }

        foreach ($this->routes[$method] as $route) {
            $regex = preg_replace('/{([a-zA-Z0-9_]+)}/', '([a-zA-Z0-9_]+)', $route['path']);
            if (preg_match("#^$regex$#", $trimmedPath, $matches)) {
                array_shift($matches);
                $routeFound = true;
                $this->params = $matches;
                $this->runMiddleware(function() use ($route) {
                    call_user_func_array($route['callback'], $this->params);
                });
                break;
            }
        }

        if (!$routeFound) {
            $this->handleNotFound();
        }
    }

    /**
     * Handle 404 Not Found
     * 
     * @return void
     */
    private function handleNotFound() {
        if ($this->notFoundCallback) {
            $this->runMiddleware(function() {
                call_user_func($this->notFoundCallback, $this);
            });
        } else {
            echo "404 Not Found";
        }
    }

    /**
     * Redirect to another URL
     * 
     * @param string $url Target URL
     * @param int $statusCode HTTP status code
     * @return void
     */
    public function redirect($url, $statusCode = 302) {
        header('Location: ' . $url, true, $statusCode);
        exit();
    }

    /**
     * Set the 404 Not Found handler
     * 
     * @param callable $callback Function to handle 404 errors
     * @return void
     */
    public function setNotFound($callback) {
        $this->notFoundCallback = $callback;
    }

    /**
     * Include a component from the components folder.
     * 
     * @param string $name Name of the component file
     * @return void
     */
    public function component($name) {
        $path = 'app/components/' . ltrim($name, '/') . '.php';

        if (file_exists($path)) {
            include $path;
        } else {
            echo "Warning: Component not found ($name)";
        }
    }

    /**
     * The `render` function in PHP is responsible for rendering a view with optional layout and
     * escaping HTML entities in the provided options.
     * 
     * @param view The `render` function you provided is responsible for rendering views with optional
     * layouts in a web application. The `view` parameter represents the name of the view file to be
     * rendered. This file is typically located in the `app/views/` directory and has a `.php`
     * extension.
     * @param options The `options` parameter in the `render` function is an array that can contain
     * additional configuration options for rendering the view. These options can be used to customize
     * the rendering process based on specific requirements. In the provided code snippet, the
     * `options` array is processed to handle the layout configuration for the
     * 
     * @return The `render` function is responsible for rendering a view with optional layout in a PHP
     * application. It first determines the current path from the server request URI, then sets the
     * layout based on the provided options or the default layout for the current path. It then
     * sanitizes the options array by applying `htmlspecialchars` to all string values.
     */
    public function render($view, $options = []) {
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $currentPath = $this->trim($currentPath);
        
        $layout = $options['layout'] ?? $this->getEffectiveLayout($currentPath);
        unset($options['layout']);
    
        $options = array_map(function($value) {
            return is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
        }, $options);
    
        extract($options);
    
        $component = [$this, 'component'];
    
        if ($layout === false) {
            require 'app/views/' . $view . '.php';
        } else {
            ob_start();
            require 'app/views/' . $view . '.php';
            $content = ob_get_clean();

            require 'app/layouts/' . $layout . '.php';
        }
    }

    /**
     * Get the effective layout for current path
     * 
     * @param string $path Current request path
     * @return string|null Layout name
     */
    private function getEffectiveLayout($path) {
        $matchingLayout = null;
        $longestMatch = 0;

        foreach ($this->groupLayouts as $groupPrefix => $layout) {
            if (strpos($path, $groupPrefix) === 0 && strlen($groupPrefix) > $longestMatch) {
                $matchingLayout = $layout;
                $longestMatch = strlen($groupPrefix);
            }
        }

        return $matchingLayout ?? $this->layout;
    }

    /**
     * Run middleware stack
     * 
     * @param callable $next Next middleware/route handler
     * @return void
     */
    private function runMiddleware($next) {
        $middlewareChain = $this->middleware;
        $middlewareChain[] = $next;

        $runner = function($stack) use (&$runner) {
            if (!empty($stack)) {
                $middleware = array_shift($stack);
                $middleware(function() use ($stack, $runner) {
                    $runner($stack);
                });
            }
        };

        $runner($middlewareChain);
    }

    /**
     * Trim trailing slashes from path
     * 
     * @param string $path Path to trim
     * @return string Trimmed path
     */
    private function trim($path) {
        return rtrim($path, '/');
    }
}