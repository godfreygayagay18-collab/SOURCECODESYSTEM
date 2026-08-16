// --- PART 1: TESTING AS ADMIN ---

fetch('http://localhost/free_sourcecode/api/login.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  credentials: 'same-origin',
  body: JSON.stringify({ username: 'GODFREY', password: 'PASSWORD_HERE' })
}).then(r => r.json()).then(data => console.log(data));

fetch('http://localhost/free_sourcecode/api/session.php')
.then(r => r.json()).then(data => console.log(data));

fetch('http://localhost/free_sourcecode/api/source_codes.php')
.then(r => r.json()).then(data => console.log(data));

fetch('http://localhost/free_sourcecode/api/source_codes.php?id=5')
.then(r => r.json()).then(data => console.log(data));

fetch('http://localhost/free_sourcecode/api/source_codes.php?search=pos')
.then(r => r.json()).then(data => console.log(data));

fetch('http://localhost/free_sourcecode/api/admin.php?resource=users')
.then(r => r.json()).then(data => console.log(data));

fetch('http://localhost/free_sourcecode/api/admin.php?resource=users&status=pending')
.then(r => r.json()).then(data => console.log(data));

fetch('http://localhost/free_sourcecode/api/admin.php?resource=requests')
.then(r => r.json()).then(data => console.log(data));

fetch('http://localhost/free_sourcecode/api/admin.php?resource=approve_user', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  credentials: 'same-origin',
  body: JSON.stringify({ user_id: 29 })
}).then(r => r.json()).then(data => console.log(data));

fetch('http://localhost/free_sourcecode/api/admin.php?resource=update_request_status', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  credentials: 'same-origin',
  body: JSON.stringify({ request_id: 1, status: 'Approved' })
}).then(r => r.json()).then(data => console.log(data));

fetch('http://localhost/free_sourcecode/api/messages.php?user_id=29')
.then(r => r.json()).then(data => console.log(data));

fetch('http://localhost/free_sourcecode/api/messages.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  credentials: 'same-origin',
  body: JSON.stringify({ receiver_id: 29, message: 'Hello from Admin!' })
}).then(r => r.json()).then(data => console.log(data));

fetch('http://localhost/free_sourcecode/api/logout.php', { method: 'POST' })
.then(r => r.json()).then(data => console.log(data));


// --- PART 2: TESTING AS A REGULAR USER ---

fetch('http://localhost/free_sourcecode/api/login.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  credentials: 'same-origin',
  body: JSON.stringify({ username: 'vincent', password: 'VINCENTS_PASSWORD' })
}).then(r => r.json()).then(data => console.log(data));

fetch('http://localhost/free_sourcecode/api/profile.php')
.then(r => r.json()).then(data => console.log(data));

fetch('http://localhost/free_sourcecode/api/profile.php', {
  method: 'PUT',
  headers: { 'Content-Type': 'application/json' },
  credentials: 'same-origin',
  body: JSON.stringify({
    firstname: 'Vincent',
    lastname: 'Dela Cruz',
    address: 'Villasis, Pangasinan',
    school_attended: 'Sample University',
    mobile_email: 'vincent@example.com'
  })
}).then(r => r.json()).then(data => console.log(data));

fetch('http://localhost/free_sourcecode/api/download_requests.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  credentials: 'same-origin',
  body: JSON.stringify({ code_id: 5, gcash_ref: 'TEST123' })
}).then(r => r.json()).then(data => console.log(data));

fetch('http://localhost/free_sourcecode/api/download_requests.php')
.then(r => r.json()).then(data => console.log(data));

fetch('http://localhost/free_sourcecode/api/messages.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  credentials: 'same-origin',
  body: JSON.stringify({ receiver_id: 1, message: 'Hello Admin, quick question.' })
}).then(r => r.json()).then(data => console.log(data));

fetch('http://localhost/free_sourcecode/api/messages.php')
.then(r => r.json()).then(data => console.log(data));

fetch('http://localhost/free_sourcecode/api/profile.php', {
  method: 'PUT',
  headers: { 'Content-Type': 'application/json' },
  credentials: 'same-origin',
  body: JSON.stringify({
    new_password: 'NewP@ssword123',
    confirm_password: 'NewP@ssword123'
  })
}).then(r => r.json()).then(data => console.log(data));

fetch('http://localhost/free_sourcecode/api/logout.php', { method: 'POST' })
.then(r => r.json()).then(data => console.log(data));


// --- PART 3: TESTING WITHOUT BEING LOGGED IN ---

fetch('http://localhost/free_sourcecode/api/register.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    username: 'testuser1',
    password: 'Test@Pass123',
    firstname: 'Test',
    lastname: 'User',
    address: 'Sample Address',
    school_attended: 'Sample School',
    mobile_email: 'testuser1@example.com'
  })
}).then(r => r.json()).then(data => console.log(data));

fetch('http://localhost/free_sourcecode/api/session.php')
.then(r => r.json()).then(data => console.log(data));

fetch('http://localhost/free_sourcecode/api/profile.php')
.then(r => r.json()).then(data => console.log(data));