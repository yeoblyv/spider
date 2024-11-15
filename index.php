<?php

// Require the main file of the Spider framework.
require_once "spider/spider.php";

/**
 * Start the Spider web engine.
 *
 * - Initializes core components of the framework.
 * - Loads registered modules and prepares the application for web requests.
 */
Spider::web();

/**
 * Set the root path for the application.
 *
 * - Defines the base directory for accessing public and private resources.
 * - Uses the server's DOCUMENT_ROOT to determine the path.
 */
Response::setRootPath();

/**
 * Set the default page format.
 *
 * - Specifies the content format for the response (e.g., HTML, JSON).
 * - Sets the `Content-Type` header for the response.
 */
Response::setPageFormat();

/**
 * Process the incoming HTTP request.
 *
 * - Retrieves the current URI using `Request::get()`.
 * - Determines the file or resource to be served.
 * - Sends the file content if it exists and is accessible.
 * - Returns a 404 status code if the file is not found.
 */
Response::processRequest(Request::get());
