<?php
namespace Sphp\Core;

class View
{
  public static function render($filename, $data = [])
  {
    extract($data);

    $baseViewDir = __DIR__ . '/../../app/views/';
    $viewPath = $baseViewDir . ltrim($filename, '/');
    if (file_exists($viewPath)) {
      // Load file content
      $content = file_get_contents($viewPath);

      $content = preg_replace_callback("/@layout\('([^']+)'(?:\s*,\s*(\[.*?\]))?\)/", function ($matches) use ($baseViewDir) {
        $layoutPath = $baseViewDir . 'layout/' . $matches[1] . '.php';
        $variables = isset($matches[2]) ? eval ('return ' . $matches[2] . ';') : [];

        if (file_exists($layoutPath)) {
          ob_start();
          extract($variables);
          include $layoutPath;
          return ob_get_clean();
        }
        return "<!-- Layout '{$matches[1]}' not found -->";
      }, $content);

      $content = preg_replace_callback("/@component\('([^']+)'(?:\s*,\s*(\$[\w]+))?\)/", function ($matches) use ($data, $baseViewDir) {
        $componentPath = $baseViewDir . 'components/' . $matches[1] . '.php';
        $variables = [];

        if (isset($matches[2]) && isset($data[substr($matches[2], 1)])) {
          $variables = $data[substr($matches[2], 1)];
        }

        if (file_exists($componentPath)) {
          ob_start();
          extract($variables);
          include $componentPath;
          return ob_get_clean();
        }
        return "<!-- Component '{$matches[1]}' not found -->";
      }, $content);


      // Evaluate the resulting PHP content
      eval ('?>' . $content);
    } else {
      $notFoundPath = $baseViewDir . '404.html';
      if (file_exists($notFoundPath)) {
        require($notFoundPath);
      } else {
        http_response_code(404);
        echo "<!DOCTYPE html><html><body><h1>404 - View Not Found</h1></body></html>";
      }
    }
  }
}
