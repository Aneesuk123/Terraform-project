// app.js - simplified example to load menu and send orders to backend
const API_BASE = 'https://terraform-backend-app.azurewebsites.net/api';

async function fetchMenu() {
  try {
    const res = await fetch(`${API_BASE}/get_menu.php`);
    if (!res.ok) throw new Error('Failed to load menu: ' + res.status);
    const menu = await res.json();
    renderMenu(menu);
  } catch (err) {
    console.error(err);
    document.getElementById('menu').innerText = 'Failed to load menu.';
  }
}

function renderMenu(menu) {
  const container = document.getElementById('menu');
  container.innerHTML = '';
  menu.forEach(item => {
    const div = document.createElement('div');
    div.className = 'menu-item';
    div.innerHTML = `<strong>${item.name}</strong> — ₹${item.price}
      <button data-id="${item.id}" data-name="${item.name}" data-price="${item.price}">Add</button>`;
    container.appendChild(div);
  });

  // attach buttons
  document.querySelectorAll('.menu-item button').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const id = btn.getAttribute('data-id');
      const name = btn.getAttribute('data-name');
      const price = parseFloat(btn.getAttribute('data-price'));
      addToCart({id, name, price});
    });
  });
}

let cart = [];

function addToCart(item) {
  cart.push(item);
  renderCart();
}

function renderCart() {
  const c = document.getElementById('cart');
  c.innerHTML = '';
  const ul = document.createElement('ul');
  let total = 0;
  cart.forEach((it, idx) => {
    total += Number(it.price);
    const li = document.createElement('li');
    li.innerText = `${it.name} - ₹${it.price} `;
    ul.appendChild(li);
  });
  c.appendChild(ul);
  const totalDiv = document.createElement('div');
  totalDiv.innerHTML = `<strong>Total:</strong> ₹${total.toFixed(2)} <button id="placeOrder">Place order</button>`;
  c.appendChild(totalDiv);

  document.getElementById('placeOrder')?.addEventListener('click', placeOrder);
}

async function placeOrder() {
  if (cart.length === 0) return alert('Cart is empty');

  const payload = {
    items: cart,
    total_price: cart.reduce((s, it) => s + Number(it.price), 0),
    customer_name: document.getElementById('customer_name')?.value || 'Anonymous'
  };

  try {
    const res = await fetch(`${API_BASE}/add_order.php`, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Order failed');
    alert('Order placed! ID: ' + data.order_id);
    cart = [];
    renderCart();
  } catch (err) {
    console.error(err);
    alert('Failed to place order: ' + err.message);
  }
}

// boot
document.addEventListener('DOMContentLoaded', () => {
  fetchMenu();
});
