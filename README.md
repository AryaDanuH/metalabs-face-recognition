# Metalabs Face Recognition

An advanced, offline-capable AI Face Recognition Attendance System tailored for laboratory assistants (Aslabs).

## 🚀 Features

*   **100% Local AI:** Uses WebGL and TensorFlow.js (via `face-api.js`) to process face recognition entirely within the browser. No images or biometric data are sent to external cloud servers.
*   **Smart Timeframe Tracking:** Automatically cross-references scanned faces with the current day's schedule. Accurately logs attendance as "On Time", "Late", or rejects scans for Aslabs not scheduled.
*   **Admin Dashboard:** Comprehensive dashboard for managing Aslabs, assigning shifts, and viewing real-time attendance statistics.
*   **Auto-Absent Cron:** A built-in pseudo-cron script automatically flags absent Aslabs if they miss their scheduled shifts.
*   **Excel Export:** One-click CSV export of the entire attendance history logs.

## 🛠️ Installation

1.  **Clone the Repository:**
    ```bash
    git clone https://github.com/YOUR_USERNAME/metalabs-face-recognition.git
    cd metalabs-face-recognition
    ```
2.  **Database Setup:**
    *   Create a new MySQL database named `metalabs`.
    *   Import the provided `database_schema.sql` file to set up the required tables:
        ```bash
        mysql -u root -p metalabs < database_schema.sql
        ```
3.  **Configuration:**
    *   Ensure your web server (e.g., XAMPP, Apache) points to the cloned directory.
    *   If your database credentials differ from the default (`root` / empty password), update the `api/db.php` file.
4.  **Run:**
    *   Access the application via your local server (e.g., `http://localhost/metalabs`).

## 🛡️ Privacy & Security

This system was specifically designed to handle sensitive biometric data responsibly. Face descriptors (mathematical arrays representing facial features) are calculated locally and stored securely in the MySQL database. Actual video feeds and images from the Live Scanner are discarded immediately and never saved or transmitted.

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).
