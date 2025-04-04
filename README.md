# 🚀 My App

## Getting Started

### Prerequisites

Ensure you have the following installed:

- PHP (`>=8.3`)
- Composer
- MySQL
- Node.js (`>=22`)
- npm (`>=10.8`)
- Laravel (`>=10.x`)

### 📦 Installation

#### 1️⃣ Clone the repository
```
git clone https://github.com/your-repo.git
cd your-repo
```

#### 2️⃣ Install dependencies
```
composer install
```

#### 3️⃣ Set up environment
```
cp .env.example .env
php artisan key:generate
```

#### 4️⃣ Configure database
- Update .env with your database credentials.
- Run migrations:
```
php artisan migrate --seed
```

#### 5️⃣ Start the development server
```
php artisan serve
```

#### 6️⃣ Run frontend 
Frontend is run in jQeury. No need to run any npm
