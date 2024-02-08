### Laravel Headless Chrome

This is a very simple project that uses Laravel to run a headless Chrome browser and scrape a website.

It uses the [chrome-php](https://github.com/chrome-php/chrome) package to run the headless browser.

## Installation

1. Clone the repository

    ```
    git clone git@github.com:alaminfirdows/laravel-headless-chrome.git
    ```

2. Install the dependencies

    ```
    composer install
    ```

3. Copy the `.env.example` file to `.env`

    ```
    cp .env.example .env
    ```

4. Generate the application key

    ```
    php artisan key:generate
    ```

5. Create a new SQLite database

    ```
    touch database/database.sqlite
    ```

6. Run the migrations

    ```
    php artisan migrate
    ```

7. Install the NPM dependencies

    ```
    yarn install
    ```

8. Build the assets

    ```
    yarn build
    ```

9. Run the server

    ```
    php artisan serve
    ```

10. Visit the website
    [`http://localhost:8000`](http://localhost:8000)
