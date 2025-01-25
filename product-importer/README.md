Laravel Product Importer
# Laravel Product Importer
This Laravel application imports product data from a CSV file into a
MySQL database while applying specific business rules. It includes
features for testing and reporting skipped and successfully imported
rows.
---
## Features
- Reads product data from a `stock.csv` file.
- Applies import rules:
 - Skip items priced < $5 and stock < 10.
 - Skip items priced > $1000.
 - Import discontinued items with the current date as the
`discontinued_date`.
- Reports processed, successful, and skipped rows.
- Supports test mode (`--test`) to simulate the import process without
affecting the database.
- Fully tested with PHPUnit.
---
## Prerequisites
Ensure the following are installed on your system:
- PHP 8.0 or higher
- Composer
- MySQL
- Git
- Node.js (optional, for frontend assets)
---
## Setup and Installation
### 1. Clone the Repository
Clone the repository to your local machine:
```bash
git clone https://github.com/your-username/product-importer.git
cd product-importer
```
### 2. Install Dependencies
Install PHP dependencies using Composer:
```bash
composer install
```
```bash
```
### 3. Configure the Environment
Copy the example `.env` file and configure your database credentials:
```bash
cp .env.example .env
```
Edit the `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=product_importer
DB_USERNAME=root
DB_PASSWORD=your_password
```
### 4. Set Up the Database
Run migrations to create the required database tables:
```bash
php artisan migrate
```
(Optional) Import additional database schema or data from
`make_database.sql`:
```bash
mysql -u root -p product_importer < make_database.sql
```
### 5. Add the CSV File
Ensure the `stock.csv` file is placed in the `storage/app/` directory:
```bash
mv /path/to/stock.csv storage/app/
```
---
## Running the Application
### Start the Development Server
Run the Laravel development server:
```bash
php artisan serve
```
Access the application in your browser at:
[http://127.0.0.1:8000](http://127.0.0.1:8000)
### Import Products from CSV
Run the command to import products:
```bash
php artisan stock:import
```
### Simulate Import (Test Mode)
Run the command in test mode to simulate the import process
without saving to the database:
```bash
php artisan stock:import --test
```
---
## Testing the Application
This project includes both unit and feature tests to validate
functionality.
### Run Tests
Run the test suite with:
```bash
php artisan test
```
---
## Example Output
Running the `stock:import` command provides output like:
```
Processed: 29, Successful: 25, Skipped: 4
```
---
## Import Rules
The application applies the following rules during the import:
1. Skip rows where the price is < $5 **and** stock is < 10.
2. Skip rows where the price is > $1000.
3. Import discontinued items and set their `discontinued_date` to the
current date.
4. Log and skip rows with invalid or incomplete data.
---
## Troubleshooting
### Common Issues
- **CSV file not found**:
 Ensure the `stock.csv` file exists in the `storage/app/` directory.
- **Database connection issues**:
 Verify that your `.env` file has the correct database credentials.
### Debugging
Add debug statements in `ImportStock.php` for additional visibility:
```php
$this->info("Processing row: " . json_encode($data));
```