Register a user
POST http://localhost/library/book-authenticate.php/register
Body: { "username": "admin", "password": "password123" }

Login to get JWT token
POST http://localhost/library/book-authenticate.php/login
Body: { "username": "admin", "password": "password123" }

Access secured book API
GET http://localhost/library/books.php
Headers: Authorization: Bearer YOUR_TOKEN_HERE
