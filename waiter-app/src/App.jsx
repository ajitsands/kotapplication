import React, { useState, useEffect } from 'react';
import './App.css';

function App() {
  const [user, setUser] = useState(null);
  const [settings, setSettings] = useState(null);
  const [categories, setCategories] = useState([]);
  const [products, setProducts] = useState([]);
  const [tables, setTables] = useState([]);
  
  // Navigation / View states
  const [activeView, setActiveView] = useState('tables'); // 'tables', 'order', 'basket'
  const [selectedTable, setSelectedTable] = useState(null);
  const [activeOrder, setActiveOrder] = useState(null);
  
  // Basket & Search states
  const [basket, setBasket] = useState({});
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('all');
  
  // Kitchen Notifications (items marked ready)
  const [notifications, setNotifications] = useState([]);
  const [servedItems, setServedItems] = useState([]);

  const [theme, setTheme] = useState(localStorage.getItem('theme') || 'dark');
  const [showSandsModal, setShowSandsModal] = useState(false);

  useEffect(() => {
    if (theme === 'light') {
      document.body.classList.add('light-theme');
    } else {
      document.body.classList.remove('light-theme');
    }
    localStorage.setItem('theme', theme);
  }, [theme]);

  const toggleTheme = () => {
    setTheme(prev => prev === 'light' ? 'dark' : 'light');
  };

  // Auth Inputs
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [loginError, setLoginError] = useState('');

  // Check login session on load
  useEffect(() => {
    fetch('/api/settings')
      .then(res => res.json())
      .then(data => setSettings(data))
      .catch(err => console.error("Failed to load settings on startup", err));

    fetch('/api/user')
      .then(res => res.json())
      .then(data => {
        if (data.logged_in) {
          setUser(data.user);
          loadAppData();
        }
      })
      .catch(err => console.error("Session check failed", err));
  }, []);

  // Poll for table updates and waiter notifications
  useEffect(() => {
    if (!user) return;

    const interval = setInterval(() => {
      loadTablesState();
      loadNotifications();
    }, 4000);

    return () => clearInterval(interval);
  }, [user]);

  const loadAppData = () => {
    // Load Settings
    fetch('/api/settings')
      .then(res => res.json())
      .then(data => setSettings(data));

    // Load Categories
    fetch('/api/categories')
      .then(res => res.json())
      .then(data => setCategories(data));

    // Load Products
    fetch('/api/products')
      .then(res => res.json())
      .then(data => setProducts(data));

    loadTablesState();
    loadNotifications();
  };

  const loadTablesState = () => {
    fetch('/api/tables')
      .then(res => res.json())
      .then(data => setTables(data))
      .catch(err => console.error("Error loading tables", err));
  };

  const loadNotifications = () => {
    fetch('/api/notifications')
      .then(res => res.json())
      .then(data => {
        setNotifications(data.notifications || []);
      })
      .catch(err => console.error("Error loading notifications", err));
  };

  const handleLogin = (e) => {
    e.preventDefault();
    fetch('/api/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username, password })
    })
    .then(res => {
      if (!res.ok) throw new Error("Invalid login credentials");
      return res.json();
    })
    .then(data => {
      if (data.success) {
        setUser(data.user);
        loadAppData();
      }
    })
    .catch(err => {
      setLoginError(err.message);
    });
  };

  const handleLogout = () => {
    fetch('/logout').then(() => {
      setUser(null);
      setActiveView('tables');
    });
  };

  const changePasswordPrompt = () => {
    Swal.fire({
      title: 'Change Password',
      html:
        '<input id="swal-current-password" class="swal2-input" type="password" placeholder="Current Password">' +
        '<input id="swal-new-password" class="swal2-input" type="password" placeholder="New Password">',
      focusConfirm: false,
      showCancelButton: true,
      confirmButtonText: 'Change Password',
      showLoaderOnConfirm: true,
      background: theme === 'light' ? '#fff' : '#111827',
      color: theme === 'light' ? '#1f2937' : '#f3f4f6',
      preConfirm: () => {
        const currentPassword = document.getElementById('swal-current-password').value;
        const newPassword = document.getElementById('swal-new-password').value;
        if (!currentPassword || !newPassword) {
          Swal.showValidationMessage('Both fields are required');
          return false;
        }
        return fetch('/user/change-password', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            current_password: currentPassword,
            new_password: newPassword
          })
        })
        .then(response => {
          return response.json().then(data => {
            if (!response.ok) {
              throw new Error(data.error || response.statusText);
            }
            return data;
          });
        })
        .catch(error => {
          Swal.showValidationMessage(`Request failed: ${error.message || error}`);
        });
      },
      allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
      if (result.isConfirmed && result.value && result.value.success) {
        Swal.fire({
          title: 'Success!',
          text: 'Your password has been successfully changed.',
          icon: 'success',
          background: theme === 'light' ? '#fff' : '#111827',
          color: theme === 'light' ? '#1f2937' : '#f3f4f6'
        });
      }
    });
  };

  // Open a dining table's details and active ticket details
  const openTable = (tableNumber) => {
    setSelectedTable(tableNumber);
    setBasket({});
    
    fetch(`/api/orders/active/${tableNumber}`)
      .then(res => res.json())
      .then(data => {
        if (data.active) {
          setActiveOrder(data);
        } else {
          setActiveOrder(null);
        }
        setActiveView('order');
      })
      .catch(err => console.error("Error fetching table details", err));
  };

  // Cart operations
  const addToBasket = (product) => {
    setBasket(prev => {
      const existing = prev[product.id];
      return {
        ...prev,
        [product.id]: {
          product_id: product.id,
          name: product.name,
          price: parseFloat(product.price),
          quantity: existing ? existing.quantity + 1 : 1,
          notes: existing ? existing.notes : ''
        }
      };
    });
  };

  const changeBasketQty = (productId, amount) => {
    setBasket(prev => {
      const existing = prev[productId];
      if (!existing) return prev;

      const newQty = existing.quantity + amount;
      if (newQty <= 0) {
        const copy = { ...prev };
        delete copy[productId];
        return copy;
      }

      return {
        ...prev,
        [productId]: { ...existing, quantity: newQty }
      };
    });
  };

  const removeFromBasket = (productId) => {
    setBasket(prev => {
      const copy = { ...prev };
      delete copy[productId];
      return copy;
    });
  };

  const updateBasketNotes = (productId, notes) => {
    setBasket(prev => {
      const existing = prev[productId];
      if (!existing) return prev;
      return {
        ...prev,
        [productId]: { ...existing, notes }
      };
    });
  };

  // Submit local basket to KOT
  const submitKot = () => {
    const itemsList = Object.values(basket);
    if (itemsList.length === 0) return;

    fetch('/api/orders', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        table_number: selectedTable,
        items: itemsList
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        setBasket({});
        setActiveView('tables');
        loadTablesState();
      }
    })
    .catch(err => console.error("Error submitting KOT", err));
  };

  // Request billing / close dining ticket
  const requestBilling = (orderId) => {
    Swal.fire({
      title: 'Close Table?',
      text: 'Are you sure you want to close this table ticket? This will lock the table and generate the final bill.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#4b5563',
      confirmButtonText: 'Yes, close it',
      background: theme === 'light' ? '#fff' : '#111827',
      color: theme === 'light' ? '#1f2937' : '#f3f4f6'
    }).then((result) => {
      if (result.isConfirmed) {
        fetch(`/api/orders/close/${orderId}`, { method: 'POST' })
          .then(res => res.json())
          .then(data => {
            setActiveView('tables');
            loadTablesState();
          })
          .catch(err => console.error("Error closing order", err));
      }
    });
  };

  // Dispatch item to table (waiter confirms delivery)
  const dispatchNotificationItem = (kotItemId) => {
    setServedItems(prev => [...prev, kotItemId]);

    fetch('/api/notifications/dispatch', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ kot_item_id: kotItemId })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        setTimeout(() => {
          loadNotifications();
          setServedItems(prev => prev.filter(id => id !== kotItemId));
        }, 1200);
      } else {
        setServedItems(prev => prev.filter(id => id !== kotItemId));
      }
    })
    .catch(err => {
      console.error("Error dispatching item", err);
      setServedItems(prev => prev.filter(id => id !== kotItemId));
    });
  };

  // Total calculations
  const getBasketTotal = () => {
    return Object.values(basket).reduce((total, item) => total + (item.price * item.quantity), 0).toFixed(3);
  };

  const getBasketCount = () => {
    return Object.values(basket).reduce((total, item) => total + item.quantity, 0);
  };

  // Filtered Products List for adding
  const filteredProducts = products.filter(prod => {
    const matchesSearch = prod.name.toLowerCase().includes(searchQuery.toLowerCase()) || 
                          (prod.description && prod.description.toLowerCase().includes(searchQuery.toLowerCase()));
    const matchesCategory = selectedCategory === 'all' || prod.category_id === parseInt(selectedCategory);
    return matchesSearch && matchesCategory && parseInt(prod.is_available) === 1;
  });

  // Views rendering
  if (!user) {
    return (
      <div className="app-container" style={{ justifyContent: 'center', position: 'relative' }}>
        <button onClick={toggleTheme} style={{ position: 'absolute', top: 20, right: 20, background: 'rgba(255,255,255,0.05)', border: '1px solid var(--card-border)', color: 'var(--text-color)', cursor: 'pointer', fontSize: 15, width: 34, height: 34, borderRadius: '50%', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', boxShadow: '0 4px 10px rgba(0,0,0,0.15)' }}>🌓</button>
        <div className="login-view">
          <div className="login-logo">W</div>
          <h2 style={{ marginBottom: 5 }}>Waiter Station</h2>
          <p style={{ color: 'var(--text-muted)', fontSize: 13, marginBottom: 25 }}>Sign in to take customer table orders</p>
          
          {loginError && <div style={{ background: 'rgba(239, 68, 68, 0.1)', color: 'var(--accent-red)', padding: 12, borderRadius: 10, fontSize: 13, marginBottom: 20 }}>{loginError}</div>}

          <form onSubmit={handleLogin}>
            <div className="form-group">
              <label className="form-label">Username</label>
              <input type="text" className="form-input" placeholder="e.g. waiter1" value={username} onChange={e => setUsername(e.target.value)} required />
            </div>
            <div className="form-group">
              <label className="form-label">Password</label>
              <input type="password" className="form-input" placeholder="••••••••" value={password} onChange={e => setPassword(e.target.value)} required />
            </div>
            <button type="submit" className="btn-login" style={{ marginTop: 10 }}>Sign In</button>
          </form>

          <div className="login-footer-react" style={{ marginTop: 30, fontSize: 11, color: 'var(--text-muted)' }}>
            Powered By <a href="#" onClick={(e) => { e.preventDefault(); setShowSandsModal(true); }} style={{ color: '#818cf8', textDecoration: 'none', fontWeight: 600 }}>SaNDS Lab</a>. All rights reserved to {settings ? settings.restaurant_name : 'Gourmet Express'}
          </div>
        </div>

        {showSandsModal && (
          <div className="sands-modal-overlay" style={{ position: 'fixed', top: 0, left: 0, width: '100%', height: '100%', background: 'rgba(11, 15, 25, 0.85)', backdropFilter: 'blur(10px)', zIndex: 2000, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <div className="sands-modal-content" style={{ background: '#ffffff', border: '1px solid rgba(0, 0, 0, 0.08)', padding: '35px 25px', borderRadius: '24px', textAlign: 'center', maxWidth: '340px', width: '90%', boxShadow: '0 20px 50px rgba(0,0,0,0.2)', position: 'relative', color: '#1f2937' }}>
              <button onClick={() => setShowSandsModal(false)} style={{ position: 'absolute', top: 15, right: 15, background: 'none', border: 'none', color: '#6b7280', fontSize: 20, cursor: 'pointer', lineHeight: 1 }}>&times;</button>
              <div style={{ marginBottom: 20 }}>
                <img src="/logos/SaNDSLab-LogoNewUpdated.png" alt="SaNDS Lab Logo" style={{ maxWidth: 220, height: 'auto', display: 'block', margin: '0 auto' }} />
              </div>
              <h3 style={{ fontSize: 18, fontWeight: 700, color: '#1f2937', marginBottom: 5, letterSpacing: '-0.5px' }}>SaNDS Lab</h3>
              <p style={{ fontSize: 13, fontWeight: 600, color: '#6b7280', marginBottom: 2, textTransform: 'uppercase', letterSpacing: '0.5px' }}>Custom Software Developers</p>
              <p style={{ fontSize: 11, fontWeight: 700, color: '#7c3aed', marginBottom: 25, textTransform: 'uppercase', letterSpacing: '1px' }}>AI Powered</p>
              
              <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
                <a href="https://www.sandslab.com" target="_blank" rel="noopener noreferrer" className="btn-login" style={{ display: 'block', width: '100%', padding: 12, borderRadius: 12, fontFamily: 'inherit', color: 'white', fontSize: 14, fontWeight: 600, textDecoration: 'none', textAlign: 'center', transition: 'all 0.3s' }}>
                  🌐 Visit Website
                </a>
                <a href="https://wa.me/97335078079" target="_blank" rel="noopener noreferrer" style={{ display: 'flex', alignItems: 'center', justifyCenter: 'center', justifyContent: 'center', gap: 8, width: '100%', backgroundColor: '#25d366', padding: 12, borderRadius: 12, fontFamily: 'inherit', color: 'white', fontSize: 14, fontWeight: 600, textDecoration: 'none', textAlign: 'center', boxShadow: '0 4px 12px rgba(37, 211, 102, 0.3)', transition: 'all 0.3s' }}>
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.513 2.262 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.503-5.739-1.45L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.42 9.864-9.864.002-2.637-1.03-5.118-2.91-6.999-1.88-1.882-4.36-2.914-7.001-2.915-5.442 0-9.867 4.42-9.871 9.866-.002 2.015.528 3.985 1.536 5.736l-.991 3.616 3.7-.977zm11.452-6.52c-.29-.145-1.716-.847-1.982-.944-.265-.098-.458-.146-.65.145-.193.292-.748.944-.917 1.138-.17.19-.338.213-.628.068-.29-.145-1.226-.452-2.336-1.443-.864-.77-1.447-1.722-1.616-2.012-.17-.29-.018-.447.127-.59.13-.13.29-.338.435-.508.145-.17.193-.29.29-.483.097-.19.048-.36-.024-.505-.072-.145-.65-1.568-.89-2.146-.233-.56-.47-.483-.65-.492-.168-.008-.362-.01-.555-.01-.193 0-.507.072-.77.36-.266.29-1.014.992-1.014 2.42 0 1.427 1.038 2.805 1.182 3 .145.195 2.043 3.12 4.95 4.377.69.298 1.23.477 1.65.61.693.22 1.325.19 1.822.115.555-.083 1.716-.7 1.96-1.375.242-.676.242-1.256.17-1.376-.073-.12-.266-.194-.556-.34z"/></svg>
                  Contact Now
                </a>
              </div>
            </div>
          </div>
        )}
      </div>
    );
  }

  const currency = settings ? settings.currency_code : 'BHD';

  return (
    <div className="app-container">
      {/* Header */}
      <header className="app-header">
        <div style={{ display: 'flex', flexDirection: 'column' }}>
          <span className="app-title">{settings ? settings.restaurant_name : 'Gourmet Express'}</span>
          <span style={{ fontSize: 10, color: 'var(--text-muted)', fontWeight: 600 }}>WAITER CONSOLE</span>
        </div>
        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
          <span className="waiter-badge" onClick={changePasswordPrompt} style={{ cursor: 'pointer' }} title="Change Password">🔑 {user.name}</span>
          <button onClick={toggleTheme} style={{ background: 'rgba(255,255,255,0.05)', border: '1px solid var(--card-border)', color: 'var(--text-color)', cursor: 'pointer', fontSize: 13, width: 28, height: 28, borderRadius: '50%', display: 'inline-flex', alignItems: 'center', justifyContent: 'center' }}>🌓</button>
          <button onClick={handleLogout} style={{ background: 'none', border: 'none', color: 'var(--accent-red)', fontWeight: 'bold', fontSize: 13, cursor: 'pointer' }}>Exit</button>
        </div>
      </header>

      {/* Real-time Kitchen Dispatch Notifications Alert Banner */}
      {notifications.length > 0 && (
        <div className="notifications-panel">
          <div style={{ fontSize: 11, fontWeight: 800, color: 'var(--accent-red)', letterSpacing: 0.5 }}>READY FOR DISPATCH ({notifications.length})</div>
          {notifications.map(note => {
            const isServed = servedItems.includes(note.kot_item_id);
            return (
              <div className="notification-item" key={note.kot_item_id}>
                <span>Table <b>{note.table_number}</b>: {note.quantity} × {note.product_name}</span>
                <button 
                  className={isServed ? "btn-served" : "btn-dispatch"} 
                  onClick={() => !isServed && dispatchNotificationItem(note.kot_item_id)}
                  disabled={isServed}
                >
                  {isServed ? "SERVED" : "Ready to Serve"}
                </button>
              </div>
            );
          })}
        </div>
      )}

      {/* Main Views Router */}
      <div className="view-content">
        
        {/* VIEW 1: Tables Map */}
        {activeView === 'tables' && (
          <div>
            <div className="section-title">Dining Table Map</div>
            <div className="tables-grid">
              {tables.map(tbl => (
                <div 
                  key={tbl.table_number} 
                  className={`table-button ${tbl.status}`} 
                  onClick={() => openTable(tbl.table_number)}
                  style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center' }}
                >
                  {tbl.waiter_name && (tbl.status === 'occupied' || tbl.status === 'billing') && (
                    <span className="table-waiter-name" style={{ fontSize: '10px', fontWeight: 'bold', textTransform: 'uppercase', color: 'var(--accent-orange)', marginBottom: '4px', maxWidth: '90%', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                      👤 {tbl.waiter_name}
                    </span>
                  )}
                  <span className="table-button-num">T{tbl.table_number}</span>
                  <span className="table-button-status">{tbl.status}</span>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* VIEW 2: Order & Item Selection Screen */}
        {activeView === 'order' && (
          <div>
            <div className="table-header-row">
              <h2 style={{ fontSize: 22, fontWeight: 800, color: 'var(--text-color)' }}>Table {selectedTable} Details</h2>
              <button className="btn-back" onClick={() => setActiveView('tables')}>Back to Map</button>
            </div>

            {/* Current Active Orders / Placed items list */}
            {activeOrder ? (
              <div className="order-items-card">
                <div style={{ fontSize: 12, fontWeight: 700, textTransform: 'uppercase', color: 'var(--text-muted)', marginBottom: 12, borderBottom: '1px solid var(--card-border)', paddingBottom: 6 }}>
                  Active Bill items ({activeOrder.items.length})
                </div>
                
                {activeOrder.items.map((item, idx) => (
                  <div className="order-item-row" key={idx}>
                    <span><b>{item.total_quantity}</b> × {item.name}</span>
                    <span className="price-text" style={{ fontStyle: 'monospace' }}>{(item.price * item.total_quantity).toFixed(3)} {currency}</span>
                  </div>
                ))}

                <div style={{ marginTop: 15, display: 'flex', gap: 10 }}>
                  <button 
                    className="btn-action" 
                    style={{ flexGrow: 1, padding: 12, borderRadius: 10, background: 'rgba(239, 68, 68, 0.1)', border: '1px solid rgba(239, 68, 68, 0.2)', color: 'var(--accent-red)', fontWeight: 700, fontSize: 13, cursor: 'pointer' }}
                    onClick={() => requestBilling(activeOrder.order.id)}
                  >
                    Close Table & Bill
                  </button>
                </div>
              </div>
            ) : (
              <div style={{ color: 'var(--accent-green)', background: 'rgba(16, 185, 129, 0.05)', border: '1px dashed rgba(16, 185, 129, 0.2)', borderRadius: 14, padding: 15, fontSize: 13, fontWeight: 600, textAlign: 'center', marginBottom: 20 }}>
                Table is empty. Place a new order below.
              </div>
            )}

            {/* Add menu items panel */}
            <div style={{ borderTop: '1px solid var(--card-border)', paddingTop: 20 }}>
              <div style={{ fontSize: 14, fontWeight: 800, color: 'var(--text-muted)', textTransform: 'uppercase', marginBottom: 12 }}>Add Items to order</div>
              
              <input 
                type="text" 
                className="product-search" 
                placeholder="Search food / beverage..." 
                value={searchQuery}
                onChange={e => setSearchQuery(e.target.value)}
              />

              <div className="categories-scroll">
                <button className={`cat-pill ${selectedCategory === 'all' ? 'active' : ''}`} onClick={() => setSelectedCategory('all')}>All</button>
                {categories.map(cat => (
                  <button key={cat.id} className={`cat-pill ${selectedCategory === cat.id ? 'active' : ''}`} onClick={() => setSelectedCategory(cat.id)}>
                    {cat.name}
                  </button>
                ))}
              </div>

              <div className="products-list-scroll">
                {filteredProducts.map(prod => (
                  <div className="product-row-card" key={prod.id} onClick={() => addToBasket(prod)}>
                    <div>
                      <div className="product-row-name">{prod.name}</div>
                      <div className="product-row-price">{parseFloat(prod.price).toFixed(3)} {currency}</div>
                    </div>
                    <button className="btn-qty-mini">+</button>
                  </div>
                ))}
              </div>
            </div>
          </div>
        )}

      </div>

      {/* VIEW 3: Sticky Bottom Cart Drawer */}
      {getBasketCount() > 0 && activeView === 'order' && (
        <div className="basket-drawer">
          <div className="basket-details">
            <span className="basket-count">{getBasketCount()} {getBasketCount() === 1 ? 'Item' : 'Items'} selected</span>
            <span className="basket-total">{getBasketTotal()} {currency}</span>
          </div>
          <button className="btn-basket-view" onClick={() => setActiveView('basket')}>Review KOT</button>
        </div>
      )}

      {/* VIEW 4: Review KOT Drawer / Checkout Modal */}
      {activeView === 'basket' && (
        <div className="checkout-modal">
          <div className="checkout-header">
            <span className="checkout-title">Review KOT (Table {selectedTable})</span>
            <button className="btn-back" onClick={() => setActiveView('order')}>Cancel</button>
          </div>

          <div className="checkout-body">
            {Object.values(basket).map(item => (
              <div className="checkout-item-card" key={item.product_id}>
                <div className="checkout-item-meta">
                  <div style={{ display: 'flex', alignItems: 'center', gap: 12, flex: 1 }}>
                    <button 
                      className="btn-remove-item" 
                      onClick={() => removeFromBasket(item.product_id)}
                      title="Remove item"
                      style={{ padding: 4 }}
                    >
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                      </svg>
                    </button>
                    <div style={{ textAlign: 'left' }}>
                      <div style={{ fontWeight: 700, fontSize: 15, textAlign: 'left' }}>{item.name}</div>
                      <div style={{ fontSize: 12, color: 'var(--accent-green)', fontWeight: 600, textAlign: 'left' }}>{(item.price * item.quantity).toFixed(3)} {currency}</div>
                    </div>
                  </div>
                  <div className="cart-qty-control">
                    <button className="btn-qty-mini" onClick={() => changeBasketQty(item.product_id, -1)}>-</button>
                    <span style={{ fontWeight: 'bold', fontSize: 14, minWidth: 20, textAlign: 'center' }}>{item.quantity}</span>
                    <button className="btn-qty-mini" onClick={() => changeBasketQty(item.product_id, 1)}>+</button>
                  </div>
                </div>
                <input 
                  type="text" 
                  className="item-notes-input" 
                  placeholder="Kitchen instructions (e.g. no spice, extra hot)" 
                  value={item.notes}
                  onChange={e => updateBasketNotes(item.product_id, e.target.value)}
                />
              </div>
            ))}
          </div>

          <div className="checkout-footer">
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 15 }}>
              <span style={{ fontWeight: 'bold', fontSize: 15 }}>KOT Subtotal:</span>
              <span style={{ fontWeight: 800, fontSize: 20, color: 'var(--accent-green)' }}>{getBasketTotal()} {currency}</span>
            </div>
            <button className="btn-login" onClick={submitKot}>Confirm & Send KOT to Kitchen</button>
          </div>
        </div>
      )}

    </div>
  );
}

export default App;
