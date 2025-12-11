const API = "https://terraform-backend-app.azurewebsites.net/api";

// Load menu
fetch(`${API}/get_menu.php`)
    .then(res => res.json())
    .then(data => {
        let menu = document.getElementById("menuItems");
        data.forEach(item => {
            let opt = document.createElement("option");
            opt.value = item.name;
            opt.textContent = `${item.name} - ₹${item.price}`;
            menu.appendChild(opt);
        });
    });

// Place order
function placeOrder() {
    let customer = document.getElementById("customer").value;
    let item = document.getElementById("menuItems").value;

    fetch(`${API}/add_order.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ customer, item })
    })
    .then(res => res.text())
    .then(msg => {
        alert(msg);
        loadOrders();
    });
}

// Load orders
function loadOrders() {
    fetch(`${API}/get_orders.php`)
        .then(res => res.json())
        .then(data => {
            let rows = "";
            data.forEach(o => {
                rows += `<tr>
                    <td>${o.id}</td>
                    <td>${o.customer}</td>
                    <td>${o.item}</td>
                </tr>`;
            });
            document.querySelector("#ordersTable tbody").innerHTML = rows;
        });
}

loadOrders();
