# Product Importer

This Laravel application reads a CSV file, processes its content based on predefined rules, and imports it into a MySQL database. It supports both test mode (simulation) and actual imports.

## Features

- Reads product data from a `stock.csv` file.
- Applies business rules for importing data:
  - Items with a price < $5 and stock < 10 are skipped.
  - Items priced > $1000 are skipped.
  - Discontinued items are imported with the current date as the discontinued date.
- Generates a report of processed, successful, and skipped items.
- Includes unit and feature tests.

---

## Prerequisites

- PHP 8.0 or later
- Composer
- MySQL
- Laravel 10.x

---

## Setup Instructions

### 1. Clone the Repository
cd product-importer
2. Install Dependencies
Install PHP dependencies:

bash
Copy
Edit
composer install
Install Node.js dependencies (optional, if applicable):

bash
Copy
Edit
npm install
3. Configure Environment
Copy the example environment file and configure database credentials:

bash
Copy
Edit
cp .env.example .env
Edit .env:

env
Copy
Edit
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=product_importer
DB_USERNAME=root
DB_PASSWORD=your_password
4. Set Up Database
Run migrations to set up the database schema:

bash
Copy
Edit
php artisan migrate
If required, import the make_database.sql file into your database:

bash
Copy
Edit
mysql -u root -p product_importer < make_database.sql
5. Add the CSV File
Place the stock.csv file in the storage/app/ directory:

bash
Copy
Edit
mv /path/to/stock.csv storage/app/
Usage
Run the Import Command
To process the CSV file and import data into the database, use:

bash
Copy
Edit
php artisan stock:import
Test Mode
To simulate the import process without modifying the database:

bash
Copy
Edit
php artisan stock:import --test
Testing
This project includes unit and feature tests to validate functionality.

Run Tests
Run the test suite using:

bash
Copy
Edit
php artisan test
Example Output
When running the stock:import command, you will see output similar to:

yaml
Copy
Edit
Processed: 29, Successful: 25, Skipped: 4