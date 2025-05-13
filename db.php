<?php
// Constants for file paths
define('DATA_DIR', __DIR__ . '/data');
define('USERS_FILE', DATA_DIR . '/users.json');
define('DOCTORS_FILE', DATA_DIR . '/doctors.json');
define('PATIENTS_FILE', DATA_DIR . '/patients.json');
define('APPOINTMENTS_FILE', DATA_DIR . '/appointments.json');
define('DEPARTMENTS_FILE', DATA_DIR . '/departments.json');

// Create data directory if it doesn't exist
if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0777, true);
}

// Initialize files if they don't exist
function initializeFile($file)
{
    if (!file_exists($file)) {
        file_put_contents($file, json_encode([]));
    }
}

initializeFile(USERS_FILE);
initializeFile(DOCTORS_FILE);
initializeFile(PATIENTS_FILE);
initializeFile(APPOINTMENTS_FILE);
initializeFile(DEPARTMENTS_FILE);

// Helper function to read JSON file
function readJsonFile($file)
{
    $content = file_get_contents($file);
    return json_decode($content, true) ?? [];
}

// Helper function to write JSON file
function writeJsonFile($file, $data)
{
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

// Get all items from a file
function getAllItems($file)
{
    return readJsonFile($file);
}

// Get a single item by ID
function getItem($file, $id)
{
    $items = readJsonFile($file);
    foreach ($items as $item) {
        if ($item['id'] === $id) {
            return $item;
        }
    }
    return null;
}

// Add a new item
function addItem($file, $item)
{
    $items = readJsonFile($file);
    $item['id'] = uniqid();
    $item['created_at'] = date('Y-m-d H:i:s');
    $items[] = $item;
    return writeJsonFile($file, $items);
}

// Update an existing item
function updateItem($file, $id, $newData)
{
    $items = readJsonFile($file);
    foreach ($items as $key => $item) {
        if ($item['id'] === $id) {
            $newData['id'] = $id;
            $newData['updated_at'] = date('Y-m-d H:i:s');
            $items[$key] = array_merge($item, $newData);
            return writeJsonFile($file, $items);
        }
    }
    return false;
}

// Delete an item
function deleteItem($file, $id)
{
    $items = readJsonFile($file);
    foreach ($items as $key => $item) {
        if ($item['id'] === $id) {
            unset($items[$key]);
            return writeJsonFile($file, array_values($items));
        }
    }
    return false;
}

// Verify user credentials
function verifyUser($username, $password)
{
    $users = readJsonFile(USERS_FILE);
    foreach ($users as $user) {
        if (
            $user['username'] === $username &&
            (password_verify($password, $user['password']) || $user['password'] === $password)
        ) {
            return $user;
        }
    }
    return null;
}

// Create default admin user if it doesn't exist
$users = readJsonFile(USERS_FILE);
$adminExists = false;

foreach ($users as $user) {
    if ($user['role'] === 'admin') {
        $adminExists = true;
        break;
    }
}

if (!$adminExists) {
    $admin = [
        'id' => uniqid(),
        'username' => 'admin',
        'password' => 'admin123',
        'fullName' => 'Administrator',
        'email' => 'admin@hospital.com',
        'phone' => '1234567890',
        'role' => 'admin',
        'created_at' => date('Y-m-d H:i:s')
    ];
    $users[] = $admin;
    writeJsonFile(USERS_FILE, $users);
}
