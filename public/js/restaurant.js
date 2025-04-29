class RestaurantAPI {
    static async getMenu() {
        try {
            const response = await fetch('app/controllers/RestaurantController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_menu'
            });
            return await response.json();
        } catch (error) {
            console.error('Error fetching menu:', error);
            return [];
        }
    }

    static async addReservation(reservationData) {
        try {
            const formData = new FormData();
            formData.append('action', 'add_reservation');
            Object.keys(reservationData).forEach(key => {
                formData.append(key, reservationData[key]);
            });

            const response = await fetch('app/controllers/RestaurantController.php', {
                method: 'POST',
                body: formData
            });
            return await response.json();
        } catch (error) {
            console.error('Error adding reservation:', error);
            return { success: false, error: 'Failed to add reservation' };
        }
    }

    static async getReservations() {
        try {
            const response = await fetch('app/controllers/RestaurantController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_reservations'
            });
            return await response.json();
        } catch (error) {
            console.error('Error fetching reservations:', error);
            return [];
        }
    }
}

// Example usage:
document.addEventListener('DOMContentLoaded', async () => {
    // Load menu items
    const menuItems = await RestaurantAPI.getMenu();
    displayMenuItems(menuItems);

    // Handle reservation form submission
    const reservationForm = document.getElementById('reservation-form');
    if (reservationForm) {
        reservationForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(reservationForm);
            const reservationData = Object.fromEntries(formData.entries());
            const result = await RestaurantAPI.addReservation(reservationData);
            
            if (result.success) {
                alert('Reservation added successfully!');
                reservationForm.reset();
            } else {
                alert('Failed to add reservation: ' + (result.error || 'Unknown error'));
            }
        });
    }
});

function displayMenuItems(menuItems) {
    const menuContainer = document.getElementById('menu-container');
    if (menuContainer) {
        menuContainer.innerHTML = menuItems.map(item => `
            <div class="menu-item">
                <h3>${item.name}</h3>
                <p>${item.description}</p>
                <span class="price">$${item.price}</span>
            </div>
        `).join('');
    }
} 