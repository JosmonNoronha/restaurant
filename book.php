<?php
// Handle form submission, cancellation, and update
$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once 'config/db.php'; // 👈 include the connection

    // Sanitize inputs
    $name = $connection->real_escape_string($_POST['name'] ?? '');
    $email = $connection->real_escape_string($_POST['email'] ?? '');
    $phone = $connection->real_escape_string($_POST['phone'] ?? '');
    $date = $connection->real_escape_string($_POST['date'] ?? '');
    $time = $connection->real_escape_string($_POST['time'] ?? '');
    $guests = (int)($_POST['guests'] ?? 0);
    $updateBookingId = (int)($_POST['update_booking_id'] ?? 0);
    $updateName = $connection->real_escape_string($_POST['update_name'] ?? '');

    // Handle booking submission (Create)
    if (isset($_POST['submit_booking'])) {
        $sql = "INSERT INTO bookings (name, email, phone, date, time, guests)
                VALUES ('$name', '$email', '$phone', '$date', '$time', $guests)";

        if ($connection->query($sql) === TRUE) {
            $bookingId = $connection->insert_id;
            $successMessage = "Your booking has been successfully submitted! Your Booking Number is $bookingId. Please keep this for your records.";
        } else {
            $errorMessage = "Error: " . $connection->error;
        }
    }

    // Handle booking cancellation (Delete)
    if (isset($_POST['cancel_booking'])) {
        $cancelName = $connection->real_escape_string($_POST['cancel_name'] ?? '');
        $cancelBookingId = (int)($_POST['cancel_booking_id'] ?? 0);

        if (!empty($cancelName) && $cancelBookingId > 0) {
            $sql = "DELETE FROM bookings WHERE name = '$cancelName' AND id = '$cancelBookingId'";
            if ($connection->query($sql) === TRUE) {
                if ($connection->affected_rows > 0) {
                    $successMessage = "Booking with Booking Number $cancelBookingId under name $cancelName has been canceled successfully!";
                } else {
                    $errorMessage = "No booking found with Booking Number $cancelBookingId for $cancelName.";
                }
            } else {
                $errorMessage = "Error canceling booking: " . $connection->error;
            }
        } else {
            $errorMessage = "Please provide both name and a valid Booking Number to cancel a booking.";
        }
    }

    // Handle booking update (Update)
    if (isset($_POST['update_booking'])) {
        if ($updateBookingId > 0 && !empty($updateName)) {
            // Fetch existing booking details
            $sql = "SELECT * FROM bookings WHERE id='$updateBookingId' AND name='$updateName'";
            $result = $connection->query($sql);
            if ($result && $result->num_rows > 0) {
                $existingBooking = $result->fetch_assoc();

                // Use new values if provided, otherwise keep existing values
                $newName = !empty($name) ? $name : $existingBooking['name'];
                $newEmail = !empty($email) ? $email : $existingBooking['email'];
                $newPhone = !empty($phone) ? $phone : $existingBooking['phone'];
                $newDate = !empty($date) ? $date : $existingBooking['date'];
                $newTime = !empty($time) ? $time : $existingBooking['time'];
                $newGuests = ($guests > 0) ? $guests : $existingBooking['guests'];

                $sql = "UPDATE bookings SET name='$newName', email='$newEmail', phone='$newPhone', date='$newDate', time='$newTime', guests=$newGuests 
                        WHERE id='$updateBookingId' AND name='$updateName'";
                if ($connection->query($sql) === TRUE) {
                    if ($connection->affected_rows > 0) {
                        $successMessage = "Booking with Booking Number $updateBookingId has been updated successfully!";
                    } else {
                        $errorMessage = "No changes made to booking with Booking Number $updateBookingId.";
                    }
                } else {
                    $errorMessage = "Error updating booking: " . $connection->error;
                }
            } else {
                $errorMessage = "No booking found with Booking Number $updateBookingId for $updateName.";
            }
        } else {
            $errorMessage = "Please provide a valid Booking Number and name to update a booking.";
        }
    }

    $connection->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Book a Table</title>
  <link rel="stylesheet" href="styles.css" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
  <header>
    <h1>Book a Table</h1>
    <nav>
      <ul>
        <li><a href="index.html">Home</a></li>
        <li><a href="menu.php">Menu</a></li>
        <li><a href="book.php" class="active">Book</a></li>
        <li><a href="about.html">About Us</a></li>
        <li><a href="contact.html">Contact</a></li>
      </ul>
    </nav>
  </header>

  <main class="booking-page">
    <section>
      <h2>Reserve Your Spot</h2>

      <!-- Success or Error Messages -->
      <?php if (!empty($successMessage)): ?>
        <p style="color: green;"><?php echo $successMessage; ?></p>
      <?php endif; ?>

      <?php if (!empty($errorMessage)): ?>
        <p style="color: red;"><?php echo $errorMessage; ?></p>
      <?php endif; ?>

      <!-- Booking Form (Create) -->
      <h3>Make a New Booking</h3>
      <form id="booking-form" method="POST" action="book.php">
        <label for="name">Full Name:</label>
        <input type="text" id="name" name="name" required />

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required />

        <label for="phone">Phone Number:</label>
        <input type="tel" id="phone" name="phone" required />

        <label for="date">Date:</label>
        <input type="date" id="date" name="date" required />

        <label for="time">Time:</label>
        <input type="time" id="time" name="time" required />

        <label for="guests">Number of Guests:</label>
        <input type="number" id="guests" name="guests" min="1" required />

        <input type="submit" name="submit_booking" value="Book Now" />
      </form>

      <!-- Cancellation Form (Delete) -->
      <h3>Cancel a Booking</h3>
      <form id="cancel-form" method="POST" action="book.php">
        <label for="cancel_name">Full Name:</label>
        <input type="text" id="cancel_name" name="cancel_name" required placeholder="Enter the name used for booking" />

        <label for="cancel_booking_id">Booking Number:</label>
        <input type="number" id="cancel_booking_id" name="cancel_booking_id" required placeholder="Enter your Booking Number" />

        <input type="submit" name="cancel_booking" value="Cancel Booking" />
      </form>

      <!-- Update Booking Form -->
      <h3>Update a Booking</h3>
      <form id="update-form" method="POST" action="book.php">
        <label for="update_name">Full Name:</label>
        <input type="text" id="update_name" name="update_name" required placeholder="Enter the name used for booking" />

        <label for="update_booking_id">Booking Number:</label>
        <input type="number" id="update_booking_id" name="update_booking_id" required placeholder="Enter your Booking Number" />

        <label for="name">New Full Name:</label>
        <input type="text" id="name" name="name" />

        <label for="email">New Email:</label>
        <input type="email" id="email" name="email" />

        <label for="phone">New Phone Number:</label>
        <input type="tel" id="phone" name="phone" />

        <label for="date">New Date:</label>
        <input type="date" id="date" name="date" />

        <label for="time">New Time:</label>
        <input type="time" id="time" name="time" />

        <label for="guests">New Number of Guests:</label>
        <input type="number" id="guests" name="guests" min="1" />

        <input type="submit" name="update_booking" value="Update Booking" />
      </form>

      <!-- Display Booking Filter Form -->
      <h3>View Your Booking</h3>
      <form id="filter-form">
        <label for="filter_name">Full Name:</label>
        <input type="text" id="filter_name" name="filter_name" required placeholder="Enter your name" />

        <label for="filter_booking_id">Booking Number:</label>
        <input type="number" id="filter_booking_id" name="filter_booking_id" required placeholder="Enter your Booking Number" />

        <button type="button" id="filter_submit">View Booking</button>
      </form>

      <!-- Display Filtered Booking (Read) -->
      <h3>Your Booking Details</h3>
      <div id="booking-details">
        <p>Please use the form above to view your specific booking.</p>
      </div>
    </section>
  </main>

  <footer>
    <p>© 2025 Amarella. All rights reserved.</p>
  </footer>

  <script>
    $(document).ready(function () {
      let today = new Date().toISOString().split("T")[0];
      $("#date").attr("min", today);

      // Handle AJAX for viewing booking
      $("#filter_submit").on("click", function () {
        let filterName = $("#filter_name").val();
        let filterBookingId = $("#filter_booking_id").val();

        if (!filterName || filterBookingId <= 0) {
          $("#booking-details").html("<p style='color: red;'>Please provide both name and a valid Booking Number.</p>");
          return;
        }

        $.ajax({
          url: "fetch_booking.php",
          method: "POST",
          data: { filter_name: filterName, filter_booking_id: filterBookingId },
          dataType: "json",
          success: function (response) {
            let bookingDetails = $("#booking-details");
            if (response.success) {
              let booking = response.data;
              bookingDetails.html(`
                <table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">
                  <thead>
                    <tr>
                      <th>Booking Number</th>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Phone</th>
                      <th>Date</th>
                      <th>Time</th>
                      <th>Guests</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>${booking.id}</td>
                      <td>${booking.name}</td>
                      <td>${booking.email}</td>
                      <td>${booking.phone}</td>
                      <td>${booking.date}</td>
                      <td>${booking.time}</td>
                      <td>${booking.guests}</td>
                    </tr>
                  </tbody>
                </table>
              `);
            } else {
              bookingDetails.html(`<p style="color: red;">${response.message}</p>`);
            }
          },
          error: function (xhr, status, error) {
            $("#booking-details").html(`<p style="color: red;">Error fetching booking: ${error}</p>`);
          }
        });
      });
    });
  </script>
</html>