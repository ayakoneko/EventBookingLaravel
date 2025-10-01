# 📅 Event Booking with Laravel

An event management and booking web application built with **Laravel**, developed as part of **7005ICT – Individual Project**.  

This system allows **Organisers** to create and manage events, while **Attendees** can register, browse events, and book spots. The project demonstrates full-stack development skills including authentication, CRUD operations, raw SQL reporting, manual validation, automated testing, and implementation of advanced features.  

---

## 🚀 Features

### 🔹 Core Requirements
- **User Types** (seeded via database seeder + self-register via form).
  - Organisers
  - Attendees 
- **Authentication**
  - Email/password login & logout.
  - User name and type displayed on every page.
- **Event Listing (Public)**
  - Upcoming events shown on homepage with pagination (8 per page).
  - Event details include title, description, time, location, capacity, and organiser.
- **Event Management (Organisers)**
  - Create, update, and delete events (CRUD).
  - Validation for inputs (title, description, date, location, capacity).
  - Delete restricted if bookings exist.
- **Bookings (Attendees)**
  - Book available upcoming events.
  - Cannot book the same event twice.
  - Cannot book if event is full (manual validation in controller).
  - View all personal bookings in “My Bookings”.
- **Dashboard & Reporting (Organisers)**
  - Dashboard shows report of events created with:
    - Event Title
    - Event Date
    - Total Capacity
    - Current Bookings
    - Remaining Spots
  - Report uses **raw SQL query** for data aggregation.
- **Privacy Policy Integration**
  - Consent checkbox required during registration.
  - Server-side validation ensures users must agree before account creation.

### ✨ Advanced Requirements – Event Waiting List
- **Attendees**
  - Can join a waitlist if an event is full.
  - Can view which waitlists they are currently on.
  - Can leave a waitlist at any time.
- **Organisers**
  - Can view the list of attendees on the waitlist for their events.
- **Underlying Principles**
  - **Data Persistence:** Waitlist records are stored reliably in the database.
  - **Conditional UI:** Waitlist options are only shown when an event is full and the user is eligible to join.
- **Advanced Feature**
  - **Automated Notification:** When a booking is cancelled for a full event, the system automatically sends an email notification to the first attendee on the waitlist.  
      - Tested using `Mail::fake()` and demonstrated via Laravel’s log mailer.

### 🏆 Custom Advanced Feature – Automatic Waitlist Expiry & Promotion System
In addition to the basic waitlist notifications, this project implements a **scheduler-driven automated expiry and promotion process**:

1. When an attendee is offered a spot from the waitlist, they must claim it within a fixed time window.  
2. If the offer expires, their waitlist entry is moved to the **end of the queue** and all positions behind are compacted for fairness.  
3. The **next eligible attendee** is automatically promoted and notified by email.  
4. This sweep runs **periodically via Laravel’s scheduler**, ensuring the system operates continuously without manual intervention.  

📌 **Note:** To demo/test this feature, run the Laravel scheduler in a terminal:  
```bash
php artisan schedule:work
```

This system improves the user experience by ensuring fairness, reducing organiser workload, and providing clear, automated handling of expired waitlist offers.

---

## 🧪 Automated Testing

Comprehensive **Feature Tests** implemented with **PHPUnit** and Laravel’s testing framework:

- **Guest Access**
  - Can view events, paginate listings, and see event details.
  - Redirected when accessing protected routes.
- **Attendee Actions**
  - Register, login/logout, book events, view bookings.
  - Validation: Cannot double-book or book full events.
- **Organiser Actions**
  - Login and access dashboard.
  - Create, update, and delete their own events.
  - Restricted from editing or deleting others’ events or events with bookings.
- **User Registration**
  - Cannot register without agreeing to Privacy Policy.
- **Advanced Feature**
  - Can join a waitlist if an event is full. 
  - Can view and leave their waitlists.
  - Can view the waitlist for their events (restricted to the event owner).
  - Cannot join waitlist if seats are available or if already booked/joined.
  - Leaving waitlist compacts queue positions.

---

## 🛠️ Technologies

- **Backend:** Laravel (PHP)  
- **Database:** SQLite (Eloquent ORM + raw SQL for reporting)  
- **Frontend:** Blade templates, Bootstrap
- **Authentication:** Laravel Breeze / Auth scaffolding  
- **Testing:** PHPUnit, Laravel Feature Tests  

---

## ⚙️ Installation & Setup

```bash
# Clone the repo
git clone https://github.com/ayakoneko/EventBookingLaravel.git
cd EventBookingLaravel

# Install dependencies
composer install
npm install && npm run dev

# Set up environment
cp .env.example .env
php artisan key:generate

# Configure DB in .env
DB_DATABASE=eventbooking
DB_USERNAME=root
DB_PASSWORD=secret

# Run migrations and seeders
php artisan migrate --seed

# Run Laravel task scheduler
php artisan schedule:work
