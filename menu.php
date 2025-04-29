<?php
require_once 'config/db.php'; // Include database connection

// Fetch menu items grouped by category
$categories = ['Appetizers', 'Main Courses', 'Desserts'];
$menu_items = [];

foreach ($categories as $category) {
    $sql = "SELECT * FROM menu_items WHERE category = '$category'";
    $result = $connection->query($sql);
    if ($result) {
        $menu_items[$category] = [];
        while ($row = $result->fetch_assoc()) {
            $menu_items[$category][] = $row;
        }
    }
}

// Handle order submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['cart_data'])) {
    $cart_data = json_decode($_POST['cart_data'], true);
    $total_price = floatval($_POST['total_price']);

    $items_json = json_encode($cart_data);

    $sql = "INSERT INTO orders (total_price, items) VALUES (?, ?)";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("ds", $total_price, $items_json);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Order placed successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error placing order: ' . $connection->error]);
    }

    $stmt->close();
    $connection->close();
    exit;
}

$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Our Menu</title>
    <link rel="stylesheet" href="styles.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
    />
  </head>
  <body>
    <header>
      <h1>Our Menu</h1>
      <nav>
        <ul>
          <li><a href="index.html">Home</a></li>
          <li><a href="menu.php" class="active">Menu</a></li>
          <li><a href="book.php">Book</a></li>
          <li><a href="about.html">About Us</a></li>
          <li><a href="contact.html">Contact</a></li>
        </ul>
      </nav>
    </header>
    <main class="menu-page">
      <?php foreach ($menu_items as $category => $items): ?>
        <section>
          <h2><?php echo $category; ?></h2>
          <div class="menu-section">
            <?php foreach ($items as $item): ?>
              <div class="menu-item">
                <img src="<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>" />
                <div>
                  <h3><?php echo $item['name']; ?></h3>
                  <p><?php echo $item['description']; ?></p>
                  <span class="price">₹<?php echo number_format($item['price'], 2); ?></span>
                  <button class="add-to-cart-btn">
                    <i class="bi bi-cart-plus-fill"></i>
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endforeach; ?>
    </main>

    <!-- Floating Cart Button -->
    <button class="cart-toggle-btn">
      <i class="bi bi-cart"></i>
    </button>

    <!-- Cart Modal -->
    <div class="cart-overlay"></div>
    <div class="cart-modal">
      <h2>Your Cart</h2>
      <div class="cart-items"></div>
      <div class="cart-total">Total: ₹0.00</div>
      <button class="place-order-btn">Place Order</button>
      <button class="cart-close-btn">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <footer>
      <p>© 2025 Amarella. All rights reserved.</p>
    </footer>

    <script>
      $(document).ready(function () {
        let cart = {};

        $(".add-to-cart-btn").on("click", function () {
          let item = $(this).closest(".menu-item");
          let itemName = item.find("h3").text();
          let itemPrice = parseFloat(
            item.find(".price").text().replace("₹", "")
          );
          let button = $(this);

          if (!cart[itemName]) {
            cart[itemName] = { price: itemPrice, quantity: 1 };
            button.prop("disabled", true);
            button.html('<i class="bi bi-cart-check-fill"></i>');
          } else {
            cart[itemName].quantity++;
          }

          updateCart();
        });

        function updateCart() {
          $(".cart-items").empty();
          let total = 0;

          $.each(cart, (name, details) => {
            total += details.price * details.quantity;

            $(".cart-items").append(
              `<div class="cart-item">
                    <span>${name} - ₹${details.price.toFixed(2)}</span>
                    <input type="number" class="quantity-input" data-name="${name}" value="${
                details.quantity
              }" min="1">
                    <button class="remove-btn" data-name="${name}">Remove</button>
                </div>`
            );
          });

          $(".cart-total").text(`Total: ₹${total.toFixed(2)}`);

          $(".quantity-input").on("change", function () {
            let name = $(this).data("name");
            let newQuantity = parseInt($(this).val());

            if (newQuantity > 0) {
              cart[name].quantity = newQuantity;
            } else {
              delete cart[name];
            }
            updateCart();
          });

          $(".remove-btn").on("click", function () {
            let name = $(this).data("name");
            delete cart[name];

            let button = $(`.menu-item:contains(${name}) .add-to-cart-btn`);
            button
              .prop("disabled", false)
              .html('<i class="bi bi-cart-plus-fill"></i>');

            updateCart();
          });
        }

        // Cart Modal Toggle
        $(".cart-toggle-btn").on("click", function () {
          $(".cart-modal, .cart-overlay").fadeIn();
        });

        $(".cart-close-btn, .cart-overlay").on("click", function () {
          $(".cart-modal, .cart-overlay").fadeOut();
        });

        // Place Order with AJAX
        $(".place-order-btn").on("click", function () {
          if ($.isEmptyObject(cart)) {
            alert("Your cart is empty!");
            return;
          }

          let cartData = JSON.stringify(cart);
          let totalPrice = parseFloat($(".cart-total").text().replace("Total: ₹", ""));

          $.ajax({
            url: 'menu.php',
            method: 'POST',
            data: { cart_data: cartData, total_price: totalPrice },
            dataType: 'json',
            success: function (response) {
              if (response.success) {
                alert(response.message);
                cart = {}; // Clear cart
                updateCart();
                $(".cart-modal, .cart-overlay").fadeOut();
                $(".add-to-cart-btn").prop("disabled", false).html('<i class="bi bi-cart-plus-fill"></i>');
              } else {
                alert(response.message);
              }
            },
            error: function (xhr, status, error) {
              alert('Error placing order: ' + error);
            }
          });
        });
      });
    </script>
  </body>
</html>