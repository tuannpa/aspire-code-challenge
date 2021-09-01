# Free time coding

**Prerequisites**

- Install php 8 or above, composer 2.0.
- Install GIT.
- Install docker (latest version), docker-compose (latest version).

---------------
**Project setup**

1. Copy .env.example to .env


2. Run composer install to fetch all necessary Laravel dependencies. Add **--ignore-platform-reqs** flag to avoid version mismatch between packages.

````
composer install --ignore-platform-reqs
````

3. Initialize docker containers using Laravel Sail:

````
./vendor/bin/sail up --build
````

- If you wish to configure a Bash alias that allows you to execute Sail's commands more easily, run the following command:

````  
alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'
````

- Then Sail is now ready to use with:

````
sail up -d // Initialize the containers

sail down -v // Stop the containers, remove volumes as well
````

4. Once all containers are up and running (this might take several minutes to install necessary dependencies of the project)


5. At the root directory of the application, run the following commands:

````
php artisan key:generate // To generate application key

sudo chmod 775 -R storage // Give read permission to storage folder as the oauth key is stored in here
````

6. SSH into the **{service_name}_laravel.test_1** container and then run migration

````
docker exec -it aspire-simple-api_laravel.test bash

php artisan migrate

php artisan passport:install // Initialize the client ID and client secret
````

7. Refer to the API guidelines section to learn more about the usage of each API.

**Database**

- Navigate to the following URL:

``http://localhost:8081``

- Use the credentials in the .env file to access the database:

````
  DB_USERNAME=sail
  
  DB_PASSWORD=password
````

**Unit Testing**

- First SSH into the **{service_name}_laravel.test_1** container:

````
docker exec -it aspire-simple-api_laravel.test bash
````

- Moving on to test execution, run the following command to execute both Feature test and Unit test

````
composer test
````

**API Guidelines**

The base URL of all APIs is: http://localhost:8080/api/v1 . Except for Authentication API, **the v1 segment is not included**.

1. Authentication API:

- POST /register : Create a new user and also provide a token.


    + Body: x-www-form-urlencoded
    
    + name: Tuan, email: npatuan.uit@gmail.com, password: test, password_confirmation: test  

- POST /login : Authenticate a user created from the registration API above. Generate access token for the given user. Use this access token to use the APIs below. 


    + Body: x-www-form-urlencoded

    + email: npatuan.uit@gmail.com, password: test

2. Customer API: 

- GET /customer-loans : Fetch all loans belong to a customer.


- GET /customer-payments : Fetch all payments belong to a customer.


- POST /customer : Create a new customer.

    Sample payload:

    <code>{
  "name": "Tester new 1",
  "phone_number": "+84936627237",
  "address": "8/12A NTT",
  "gender": "M",
  "date_of_birth": "1994-10-05",
  "credit_point": "160"
  }</code>


- PATCH /customer/{id} : Update a customer by customer id.


- GET /customer?page=1&itemsPerPage=5&order=created_at : Fetch a list of customers (pagination and order is supported).


- GET /customer/{id} : Fetch a customer by customer id.
  

- DELETE /customer/{id} : Remove a customer by customer id.


3. Product API:

- POST /product : Create a new product.

  Sample payload:

  <code>{
  "name": "Product 140 credit point",
  "type": "individual",
  "amount": 30000000,
  "description": "test 140",
  "minimum_credit_point_requirement": 140
  }</code>


- PATCH /product/{id} : Update a product by product id.


- GET /product?page=1&itemsPerPage=5&order=created_at : Fetch a list of products (pagination and order is supported).


- GET /product/{id} : Fetch a product by product id.


- DELETE /product/{id} : Remove a product by product id.


4. Loan API:

- POST /loan : Create a new loan. When a loan is created, its status will be defaulted to New.

  Sample payload:

  <code>
  {
  "customer_id": 2,
  "product_id": 2,
  "description": "Description",
  "interest_rate": 7.00,
  "amount": 4700000,
  "duration": "45"
  }
  </code>


- PATCH /loan/{id} : Update a loan by loan id. 
  + Approve: Suppose there is a request to approve the given loan, system will verify if customer's credit point satisfies the product minimum credit point requirement to determine if the loan can be approved.
  + Complete: Suppose there is a request to complete the given loan, meaning the loan is completely paid, system is going to check the remaining amount of payment made for the given loan.


- GET /loan?page=1&itemsPerPage=5&order=created_at : Fetch a list of loans (pagination and order is supported).


- GET /loan/{id} : Fetch a loan by loan id.


- DELETE /loan/{id} : Remove a loan by loan id.

5. Payment API:

- POST /payment : Create a new payment. When a payment is created, its status will be defaulted to New. System is going to give error response if received POST request for a completed loan, or in case the paid_amount exceeds the amount of a loan.

  Sample payload:

  <code>
  {
    "customer_id": 2,
    "loan_id": 2,
    "due_date": "2020-08-19 00:00:00",
    "repaid_date": "2020-08-19 00:00:00",
    "paid_amount": 48000000
  }
  </code>


- PATCH /payment/{id} : Update a loan by loan id. Give error response in case the paid_amount exceeds the amount of a loan. 


- GET /payment?page=1&itemsPerPage=5&order=created_at : Fetch a list of payments (pagination and order is supported).


- GET /payment/{id} : Fetch a payment by payment id.


- DELETE /payment/{id} : Remove a payment by payment id.
