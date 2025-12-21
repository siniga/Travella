# Database Seeders

This directory contains all the database seeders for the Travela application. Seeders populate the database with initial or sample data.

## Available Seeders

### 1. UserSeeder
**Purpose:** Creates initial users for testing and development.

**Data Created:**
- 1 Admin user (admin@travela.com)
- 3 Specific test users (John Doe, Jane Smith, Mike Johnson)
- 10 Random users via factory

**Usage:**
```bash
php artisan db:seed --class=UserSeeder
```

### 2. CountrySeeder
**Purpose:** Populates the countries table with African countries.

**Data Created:**
- Tanzania (TZ)
- Kenya (KE)
- Uganda (UG)
- Rwanda (RW)
- Burundi (BI)
- South Africa (ZA)
- Nigeria (NG)
- Ghana (GH)
- Egypt (EG)
- Morocco (MA)

**Usage:**
```bash
php artisan db:seed --class=CountrySeeder
```

### 3. ProviderSeeder
**Purpose:** Creates mobile service providers.

**Data Created:**
- TTCL (Tanzania Telecommunications Company Limited)
- Airtel
- Vodacom
- Halotel
- Safaricom
- MTN

Each provider includes metadata with logo URLs and website information.

**Usage:**
```bash
php artisan db:seed --class=ProviderSeeder
```

### 4. BundleTypeSeeder
**Purpose:** Defines the types of bundles available.

**Data Created:**
- DATA (Data only)
- VOICE (Voice only)
- SMS (SMS only)
- COMBO (Data + Voice + SMS)
- DATA_VOICE (Data + Voice)
- DATA_SMS (Data + SMS)

**Usage:**
```bash
php artisan db:seed --class=BundleTypeSeeder
```

### 5. CountryProviderSeeder
**Purpose:** Links providers to countries with specific settings.

**Data Created:**
- Tanzania: TTCL (default), Airtel, Vodacom, Halotel
- Kenya: Safaricom (default), Airtel
- Uganda: MTN (default), Airtel
- South Africa: Vodacom (default), MTN

Each relationship includes country prefix and coverage percentage in metadata.

**Usage:**
```bash
php artisan db:seed --class=CountryProviderSeeder
```

### 6. BundleSeeder
**Purpose:** Creates various mobile data and combo bundles.

**Data Created:**
Multiple bundles for different providers including:
- Daily bundles (500MB, 1GB)
- Weekly bundles (3GB, 5GB, 10GB, 15GB)
- Monthly bundles (20GB, 50GB, 100GB)
- Combo bundles with data, voice, and SMS
- Voice-only bundles

**Examples:**
- Tanzania TTCL: Daily 1GB (2,000 TZS), Weekly 10GB (15,000 TZS)
- Kenya Safaricom: Daily 1GB (100 KES), Monthly 40GB (2,000 KES)
- Combo packages with minutes and SMS included

**Usage:**
```bash
php artisan db:seed --class=BundleSeeder
```

### 7. KycSeeder
**Purpose:** Creates sample KYC (Know Your Customer) records for users.

**Data Created:**
- KYC records for up to 10 users
- Encrypted passport IDs
- Arrival and departure dates
- Travel reasons (tourism, business, education, family visit)
- Some records are verified, others pending

**Usage:**
```bash
php artisan db:seed --class=KycSeeder
```

## Running All Seeders

To run all seeders in the correct order (respecting foreign key constraints):

```bash
php artisan db:seed
```

Or:

```bash
php artisan db:seed --class=DatabaseSeeder
```

## Seeding Order

The seeders are executed in this order (defined in `DatabaseSeeder.php`):

1. UserSeeder
2. CountrySeeder
3. ProviderSeeder
4. BundleTypeSeeder
5. CountryProviderSeeder (requires Countries and Providers)
6. BundleSeeder (requires CountryProvider and BundleTypes)
7. KycSeeder (requires Users)

## Fresh Migration with Seeding

To reset the database and run all migrations and seeders:

```bash
php artisan migrate:fresh --seed
```

## Important Notes

- All seeders use `updateOrCreate()` to prevent duplicate entries
- Running seeders multiple times won't create duplicates (they're idempotent)
- Passwords for seeded users are: `password123`
- KYC passport data is encrypted using Laravel's Crypt facade
- Bundle prices are in local currencies (TZS, KES, etc.)

## Adding Custom Data

To add your own data, you can:

1. Modify existing seeders
2. Create new seeders using: `php artisan make:seeder YourSeederName`
3. Add your seeder to the `DatabaseSeeder::run()` method

## Sample Credentials

**Admin User:**
- Email: admin@travela.com
- Password: password123

**Test Users:**
- Email: john.doe@example.com | Password: password123
- Email: jane.smith@example.com | Password: password123
- Email: mike.johnson@example.com | Password: password123

