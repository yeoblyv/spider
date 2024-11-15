![repo-banner](banner.png)

# Spider: Modular Web Engine

**Spider** is a lightweight modular web engine designed for developing web applications using PHP. The primary goal of Spider is to provide a flexible architecture, easy plugin integration, and support for modern web development standards. With its adaptable structure, Spider can be used to create anything from small websites to large, high-load applications.

## Key Features

### 1. **Modularity**
Spider is built on a modular architecture, allowing developers to enable or disable plugins and components as needed. This makes it easy to extend functionality while keeping redundant code minimal.

### 2. **Component Autoloading**
Spider supports automatic loading of plugins and classes through a built-in autoloading mechanism (`spl_autoload_register`). This simplifies project structure and enables dynamic file inclusion based on their names.

### 3. **Modern Technologies**
The engine supports object-oriented programming, namespaces, and integration with popular libraries and standards, such as PDO for database management.

### 4. **Extensive Format Support**
Spider includes a built-in list of MIME types that supports a wide range of data formats, from text and graphics to multimedia and archives.

### 5. **Request and Response Management**
The engine provides powerful classes for handling HTTP requests (`Request`) and creating responses (`Response`), including managing headers, redirects, statuses, and content.

## Architecture

Spider consists of several core components:

### **1. SpiderCoreComponent**
This is the base interface implemented by all key engine classes. It ensures consistency in structure and simplifies interaction between modules.

### **2. MySQL**
A class for database interaction using PDO. It supports:
- Connecting to a database.
- Performing CRUD operations (create, read, update, delete).
- Protection against SQL injection.
- An `upsert` mechanism for updating or inserting data based on its presence.

### **3. Request**
A class for handling incoming HTTP requests. Key features include:
- Accessing query string data (`$_GET`).
- Managing HTTP statuses and redirects.
- Filtering and validating request data.

### **4. Response**
A class for creating HTTP responses. Key features include:
- Managing headers.
- Setting content type (MIME type).
- Generating and outputting data, including dynamic pages and files.

### **5. Utils**
A utility class that provides tools for working with data, generating random strings, calculating execution time, and other tasks.

## Advantages of Using Spider

1. **Simplicity and Lightweight:**
   Spider imposes no strict requirements on project structure and requires no additional tools to get started.

2. **Extensibility:**
   With plugin support, the engine can easily be adapted to meet specific project needs.

3. **Compatibility:**
   Spider supports PHP 5.3 and above, making it compatible with a wide range of server environments.

4. **Flexibility:**
   Developers can use any third-party libraries or frameworks, integrating them seamlessly with the engine.

## Usage Example

### Connecting to a Database
```php
MySQL::connect('localhost', 'example_db', 'user', 'password');
```

### Executing a Query
```php
$users = MySQL::query('SELECT * FROM users');
```

### Creating a Response
```php
Response::setPageFormat('json');
echo json_encode($users);
```

## Limitations

- **No Built-in Routing System:**
  While the engine provides basic classes for handling requests, developers are responsible for implementing routing.

- **Requires Basic PHP Knowledge:**
  Spider is not an out-of-the-box solution for beginners and requires an understanding of PHP to utilize its features.

## Conclusion

Spider is a powerful and flexible tool for building web applications. Its modular architecture, support for modern standards, and easy integration make it an attractive choice for developers looking for a lightweight and scalable web engine.