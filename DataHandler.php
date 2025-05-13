<?php
class DataHandler
{
    private $dataDir;
    private $usersFile;
    private $doctorsFile;
    private $patientsFile;
    private $appointmentsFile;

    public function __construct()
    {
        $this->dataDir = __DIR__ . '/../data/';
        $this->usersFile = $this->dataDir . 'users.json';
        $this->doctorsFile = $this->dataDir . 'doctors.json';
        $this->patientsFile = $this->dataDir . 'patients.json';
        $this->appointmentsFile = $this->dataDir . 'appointments.json';

        // Create data directory if it doesn't exist
        if (!file_exists($this->dataDir)) {
            mkdir($this->dataDir, 0777, true);
        }

        // Initialize files if they don't exist
        $this->initializeFiles();
    }

    private function initializeFiles()
    {
        // Initialize users.json
        if (!file_exists($this->usersFile)) {
            $defaultUsers = [
                'users' => [
                    [
                        'id' => 'A001',
                        'username' => 'admin',
                        'email' => 'admin@hospital.com',
                        'password' => 'admin123',
                        'role' => 'admin'
                    ]
                ]
            ];
            $this->writeJsonFile($this->usersFile, $defaultUsers);
        }

        // Initialize doctors.json
        if (!file_exists($this->doctorsFile)) {
            $this->writeJsonFile($this->doctorsFile, ['doctors' => []]);
        }

        // Initialize patients.json
        if (!file_exists($this->patientsFile)) {
            $this->writeJsonFile($this->patientsFile, ['patients' => []]);
        }

        // Initialize appointments.json
        if (!file_exists($this->appointmentsFile)) {
            $this->writeJsonFile($this->appointmentsFile, ['appointments' => []]);
        }
    }

    private function readJsonFile($file)
    {
        if (!file_exists($file)) {
            return [];
        }
        $content = file_get_contents($file);
        return json_decode($content, true) ?? [];
    }

    private function writeJsonFile($file, $data)
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);
        return file_put_contents($file, $json);
    }

    public function getAllUsers()
    {
        $data = $this->readJsonFile($this->usersFile);
        return $data['users'] ?? [];
    }

    public function getUserByEmail($email)
    {
        $users = $this->getAllUsers();
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                return $user;
            }
        }
        return null;
    }

    public function addUser($userData)
    {
        $data = $this->readJsonFile($this->usersFile);
        $data['users'][] = $userData;
        return $this->writeJsonFile($this->usersFile, $data);
    }

    public function getAllDoctors()
    {
        $data = $this->readJsonFile($this->doctorsFile);
        return $data['doctors'] ?? [];
    }

    public function addDoctor($doctorData)
    {
        $data = $this->readJsonFile($this->doctorsFile);
        $data['doctors'][] = $doctorData;
        return $this->writeJsonFile($this->doctorsFile, $data);
    }

    public function updateDoctor($id, $doctorData)
    {
        $data = $this->readJsonFile($this->doctorsFile);
        foreach ($data['doctors'] as $key => $doctor) {
            if ($doctor['id'] === $id) {
                $data['doctors'][$key] = $doctorData;
                break;
            }
        }
        return $this->writeJsonFile($this->doctorsFile, $data);
    }

    public function deleteDoctor($id)
    {
        $data = $this->readJsonFile($this->doctorsFile);
        foreach ($data['doctors'] as $key => $doctor) {
            if ($doctor['id'] === $id) {
                unset($data['doctors'][$key]);
                break;
            }
        }
        $data['doctors'] = array_values($data['doctors']);
        return $this->writeJsonFile($this->doctorsFile, $data);
    }

    public function getAllPatients()
    {
        $data = $this->readJsonFile($this->patientsFile);
        return $data['patients'] ?? [];
    }

    public function addPatient($patientData)
    {
        $data = $this->readJsonFile($this->patientsFile);
        $data['patients'][] = $patientData;
        return $this->writeJsonFile($this->patientsFile, $data);
    }

    public function updatePatient($id, $patientData)
    {
        $data = $this->readJsonFile($this->patientsFile);
        foreach ($data['patients'] as $key => $patient) {
            if ($patient['id'] === $id) {
                $data['patients'][$key] = $patientData;
                break;
            }
        }
        return $this->writeJsonFile($this->patientsFile, $data);
    }

    public function deletePatient($id)
    {
        $data = $this->readJsonFile($this->patientsFile);
        foreach ($data['patients'] as $key => $patient) {
            if ($patient['id'] === $id) {
                unset($data['patients'][$key]);
                break;
            }
        }
        $data['patients'] = array_values($data['patients']);
        return $this->writeJsonFile($this->patientsFile, $data);
    }

    public function generateId($prefix)
    {
        $timestamp = time();
        $random = rand(1000, 9999);
        return $prefix . $timestamp . $random;
    }

    // Appointment Methods
    public function getAllAppointments()
    {
        $data = $this->readJsonFile($this->appointmentsFile);
        return $data['appointments'] ?? [];
    }

    public function getAppointmentById($id)
    {
        $appointments = $this->getAllAppointments();
        foreach ($appointments as $appointment) {
            if ($appointment['id'] === $id) {
                return $appointment;
            }
        }
        return null;
    }

    public function addAppointment($appointmentData)
    {
        $data = $this->readJsonFile($this->appointmentsFile);
        $data['appointments'][] = $appointmentData;
        return $this->writeJsonFile($this->appointmentsFile, $data);
    }

    public function updateAppointment($id, $appointmentData)
    {
        $data = $this->readJsonFile($this->appointmentsFile);
        foreach ($data['appointments'] as $key => $appointment) {
            if ($appointment['id'] === $id) {
                $data['appointments'][$key] = $appointmentData;
                break;
            }
        }
        return $this->writeJsonFile($this->appointmentsFile, $data);
    }

    public function deleteAppointment($id)
    {
        $data = $this->readJsonFile($this->appointmentsFile);
        foreach ($data['appointments'] as $key => $appointment) {
            if ($appointment['id'] === $id) {
                unset($data['appointments'][$key]);
                break;
            }
        }
        $data['appointments'] = array_values($data['appointments']);
        return $this->writeJsonFile($this->appointmentsFile, $data);
    }
}
