<div class="content-scroll">
    <!-- Page Header -->
    <div class="view-header">
        <div>
            <h1>Patient Vitals & Room Assignment</h1>
            <p>Record patient vitals and assign to available rooms</p>
        </div>
        <div class="header-actions">
            <button class="btn-primary" id="quickAdmitBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="23" y1="11" x2="17" y2="11"></line>
                </svg>
                Quick Admit
            </button>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="dashboard-row">
        <!-- Left Column - Patient Selection & Vitals -->
        <div class="card">
            <div class="card-header">
                <h2>Patient Vitals Recording</h2>
                <span class="badge badge-primary" id="activePatientBadge" style="display: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    Patient Selected
                </span>
            </div>
            
            <div class="card-body" style="padding: 24px;">
                <!-- Patient Search -->
                <div class="patient-search-section">
                    <div class="search-container" style="width: 100%; margin-bottom: 24px;">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" id="patientSearch" placeholder="Search patients by name, ID, or phone number...">
                    </div>
                    
                    <!-- Selected Patient Info -->
                    <div class="selected-patient-info" id="selectedPatientInfo" style="display: none;">
                        <div class="patient-card">
                            <div class="patient-avatar">
                                <span>JD</span>
                            </div>
                            <div class="patient-details">
                                <h3>John Doe</h3>
                                <div class="patient-meta">
                                    <span class="patient-id">ID: P-2024-00123</span>
                                    <span class="patient-age">32 years</span>
                                    <span class="patient-gender">Male</span>
                                </div>
                                <div class="patient-status">
                                    <span class="status-badge status-critical">Emergency</span>
                                    <span class="arrival-time">Arrived: 10:30 AM</span>
                                </div>
                            </div>
                            <button class="btn-remove-patient" onclick="clearPatientSelection()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Vitals Form -->
                <div class="vitals-form-section">
                    <h3 style="margin-bottom: 20px; color: var(--text-main);">Record Vitals</h3>
                    
                    <form id="vitalsForm">
                        <!-- Vital Signs Grid -->
                        <div class="vitals-grid">
                            <!-- Temperature -->
                            <div class="vital-input">
                                <label for="temperature">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
                                        <path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"></path>
                                    </svg>
                                    Temperature
                                </label>
                                <div class="input-with-unit">
                                    <input type="number" id="temperature" step="0.1" placeholder="98.6" min="90" max="110">
                                    <span class="input-unit">°F</span>
                                </div>
                                <div class="vital-status" id="tempStatus">Normal</div>
                            </div>

                            <!-- Blood Pressure -->
                            <div class="vital-input">
                                <label for="bloodPressure">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
                                        <path d="M3 10h11M3 14h11M17 10h4M17 14h4M17 6h4"></path>
                                    </svg>
                                    Blood Pressure
                                </label>
                                <div class="input-with-unit">
                                    <input type="text" id="bloodPressure" placeholder="120/80" pattern="\d{2,3}/\d{2,3}">
                                    <span class="input-unit">mmHg</span>
                                </div>
                                <div class="vital-status" id="bpStatus">Normal</div>
                            </div>

                            <!-- Heart Rate -->
                            <div class="vital-input">
                                <label for="heartRate">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
                                        <path d="M20.42 4.58a5.4 5.4 0 0 0-7.65 0l-.77.78-.77-.78a5.4 5.4 0 0 0-7.65 0C1.46 6.7 1.33 10.28 4 13l8 8 8-8c2.67-2.72 2.54-6.3.42-8.42z"></path>
                                    </svg>
                                    Heart Rate
                                </label>
                                <div class="input-with-unit">
                                    <input type="number" id="heartRate" placeholder="72" min="30" max="200">
                                    <span class="input-unit">BPM</span>
                                </div>
                                <div class="vital-status" id="hrStatus">Normal</div>
                            </div>

                            <!-- Respiratory Rate -->
                            <div class="vital-input">
                                <label for="respiratoryRate">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
                                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                    </svg>
                                    Respiratory Rate
                                </label>
                                <div class="input-with-unit">
                                    <input type="number" id="respiratoryRate" placeholder="16" min="8" max="40">
                                    <span class="input-unit">Breaths/min</span>
                                </div>
                                <div class="vital-status" id="rrStatus">Normal</div>
                            </div>

                            <!-- Oxygen Saturation -->
                            <div class="vital-input">
                                <label for="oxygenSaturation">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 15 15"></polyline>
                                    </svg>
                                    O₂ Saturation
                                </label>
                                <div class="input-with-unit">
                                    <input type="number" id="oxygenSaturation" placeholder="98" min="70" max="100">
                                    <span class="input-unit">%</span>
                                </div>
                                <div class="vital-status" id="oxStatus">Normal</div>
                            </div>

                            <!-- Blood Glucose -->
                            <div class="vital-input">
                                <label for="bloodGlucose">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
                                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                        <line x1="12" y1="9" x2="12" y2="13"></line>
                                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                    </svg>
                                    Blood Glucose
                                </label>
                                <div class="input-with-unit">
                                    <input type="number" id="bloodGlucose" placeholder="100" min="40" max="400">
                                    <span class="input-unit">mg/dL</span>
                                </div>
                                <div class="vital-status" id="glucoseStatus">Normal</div>
                            </div>
                        </div>

                        <!-- Additional Notes -->
                        <div class="form-group" style="margin-top: 24px;">
                            <label for="vitalsNotes">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                                Clinical Notes
                            </label>
                            <textarea id="vitalsNotes" rows="3" placeholder="Enter any additional observations, symptoms, or concerns..."></textarea>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions" style="margin-top: 32px; display: flex; gap: 12px; justify-content: flex-end;">
                            <button type="reset" class="btn-secondary">
                                Clear Form
                            </button>
                            <button type="button" class="btn-primary" id="saveVitalsBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                    <polyline points="7 3 7 8 15 8"></polyline>
                                </svg>
                                Save Vitals
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column - Room Assignment -->
        <div class="card">
            <div class="card-header">
                <h2>Available Rooms</h2>
                <div class="room-stats">
                    <span class="stat-tag available">12 Available</span>
                    <span class="stat-tag occupied">8 Occupied</span>
                </div>
            </div>
            
            <div class="card-body" style="padding: 24px;">
                <!-- Room Filter -->
                <div class="room-filter" style="margin-bottom: 24px;">
                    <div class="filter-tabs">
                        <button class="filter-tab active" data-filter="all">All Rooms</button>
                        <button class="filter-tab" data-filter="general">General Ward</button>
                        <button class="filter-tab" data-filter="icu">ICU</button>
                        <button class="filter-tab" data-filter="emergency">Emergency</button>
                    </div>
                </div>

                <!-- Rooms Grid -->
                <div class="rooms-grid">
                    <!-- Room 101 -->
                    <div class="room-card available" data-type="general">
                        <div class="room-header">
                            <span class="room-number">101</span>
                            <span class="room-type">General</span>
                        </div>
                        <div class="room-details">
                            <div class="room-info">
                                <span class="room-bed">Bed A</span>
                                <span class="room-status available">Available</span>
                            </div>
                            <div class="room-equipment">
                                <span class="equipment-tag">Oxygen</span>
                                <span class="equipment-tag">Monitor</span>
                            </div>
                        </div>
                        <button class="assign-btn" onclick="assignToRoom(101)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>
                            Assign Patient
                        </button>
                    </div>

                    <!-- Room 102 -->
                    <div class="room-card available" data-type="general">
                        <div class="room-header">
                            <span class="room-number">102</span>
                            <span class="room-type">General</span>
                        </div>
                        <div class="room-details">
                            <div class="room-info">
                                <span class="room-bed">Bed A</span>
                                <span class="room-status available">Available</span>
                            </div>
                            <div class="room-equipment">
                                <span class="equipment-tag">Oxygen</span>
                            </div>
                        </div>
                        <button class="assign-btn" onclick="assignToRoom(102)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>
                            Assign Patient
                        </button>
                    </div>

                    <!-- Room 201 (ICU) -->
                    <div class="room-card available" data-type="icu">
                        <div class="room-header">
                            <span class="room-number">201</span>
                            <span class="room-type">ICU</span>
                        </div>
                        <div class="room-details">
                            <div class="room-info">
                                <span class="room-bed">Bed 1</span>
                                <span class="room-status available">Available</span>
                            </div>
                            <div class="room-equipment">
                                <span class="equipment-tag">Ventilator</span>
                                <span class="equipment-tag">Monitor</span>
                                <span class="equipment-tag">Defibrillator</span>
                            </div>
                        </div>
                        <button class="assign-btn" onclick="assignToRoom(201)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>
                            Assign Patient
                        </button>
                    </div>

                    <!-- Room 301 (Emergency) -->
                    <div class="room-card occupied" data-type="emergency">
                        <div class="room-header">
                            <span class="room-number">301</span>
                            <span class="room-type">Emergency</span>
                        </div>
                        <div class="room-details">
                            <div class="room-info">
                                <span class="room-bed">Trauma 1</span>
                                <span class="room-status occupied">Occupied</span>
                            </div>
                            <div class="room-equipment">
                                <span class="equipment-tag">X-Ray</span>
                                <span class="equipment-tag">Monitor</span>
                            </div>
                            <div class="occupied-info">
                                <span class="patient-in-room">Patient: Jane Smith</span>
                                <span class="occupied-time">Since: 09:15 AM</span>
                            </div>
                        </div>
                        <button class="assign-btn disabled" disabled>
                            Currently Occupied
                        </button>
                    </div>

                    <!-- Room 103 -->
                    <div class="room-card available" data-type="general">
                        <div class="room-header">
                            <span class="room-number">103</span>
                            <span class="room-type">General</span>
                        </div>
                        <div class="room-details">
                            <div class="room-info">
                                <span class="room-bed">Bed A</span>
                                <span class="room-status available">Available</span>
                            </div>
                            <div class="room-equipment">
                                <span class="equipment-tag">Oxygen</span>
                                <span class="equipment-tag">Monitor</span>
                            </div>
                        </div>
                        <button class="assign-btn" onclick="assignToRoom(103)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>
                            Assign Patient
                        </button>
                    </div>
                </div>

                <!-- Quick Assign Section -->
                <div class="quick-assign" style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--border);">
                    <h3 style="margin-bottom: 16px;">Quick Assignment</h3>
                    <div class="quick-actions">
                        <button class="quick-btn" onclick="assignEmergencyRoom()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            Emergency Room
                        </button>
                        <button class="quick-btn" onclick="assignICU()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8h1a4 4 0 0 1 0 8h-1"></path>
                                <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path>
                                <line x1="6" y1="1" x2="6" y2="4"></line>
                                <line x1="10" y1="1" x2="10" y2="4"></line>
                                <line x1="14" y1="1" x2="14" y2="4"></line>
                            </svg>
                            ICU Priority
                        </button>
                        <button class="quick-btn" onclick="assignIsolation()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                            Isolation Room
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card" style="margin-top: 24px;">
        <div class="card-header">
            <h2>Recent Vitals Recorded</h2>
        </div>
        <div class="card-body">
            <div class="recent-activity">
                <div class="activity-item">
                    <div class="activity-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.42 4.58a5.4 5.4 0 0 0-7.65 0l-.77.78-.77-.78a5.4 5.4 0 0 0-7.65 0C1.46 6.7 1.33 10.28 4 13l8 8 8-8c2.67-2.72 2.54-6.3.42-8.42z"></path>
                        </svg>
                    </div>
                    <div class="activity-details">
                        <p><strong>Robert Johnson</strong> - Vitals recorded</p>
                        <span class="activity-time">10:45 AM | Temp: 99.2°F, BP: 130/85</span>
                    </div>
                    <div class="activity-room">
                        Assigned to <span class="room-tag">Room 105</span>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.42 4.58a5.4 5.4 0 0 0-7.65 0l-.77.78-.77-.78a5.4 5.4 0 0 0-7.65 0C1.46 6.7 1.33 10.28 4 13l8 8 8-8c2.67-2.72 2.54-6.3.42-8.42z"></path>
                        </svg>
                    </div>
                    <div class="activity-details">
                        <p><strong>Maria Garcia</strong> - Vitals recorded</p>
                        <span class="activity-time">10:30 AM | Temp: 100.4°F, HR: 110 BPM</span>
                    </div>
                    <div class="activity-room">
                        Assigned to <span class="room-tag">Room 201 (ICU)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Patient Card Styles */
.patient-card {
    display: flex;
    align-items: center;
    padding: 16px;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid var(--border);
    margin-bottom: 16px;
    position: relative;
}

.patient-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 18px;
    margin-right: 16px;
    flex-shrink: 0;
}

.patient-details h3 {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 4px;
}

.patient-meta {
    display: flex;
    gap: 12px;
    margin-bottom: 8px;
    font-size: 12px;
    color: var(--text-muted);
}

.patient-status {
    display: flex;
    gap: 12px;
    align-items: center;
}

.arrival-time {
    font-size: 12px;
    color: var(--text-muted);
}

.btn-remove-patient {
    position: absolute;
    top: 12px;
    right: 12px;
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
}

.btn-remove-patient:hover {
    background: #fee2e2;
    color: #dc2626;
}

/* Vitals Grid */
.vitals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.vital-input {
    background: white;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 16px;
    transition: all 0.2s;
}

.vital-input:hover {
    border-color: var(--primary);
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.1);
}

.vital-input label {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
    font-weight: 600;
    font-size: 13px;
    color: var(--text-main);
}

.input-with-unit {
    position: relative;
}

.input-with-unit input {
    width: 100%;
    padding: 10px 40px 10px 12px;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    background: #f8fafc;
}

.input-with-unit input:focus {
    outline: none;
    border-color: var(--primary);
    background: white;
}

.input-unit {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 600;
}

.vital-status {
    margin-top: 8px;
    font-size: 11px;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 20px;
    display: inline-block;
    background: #10b981;
    color: white;
}

/* Room Cards */
.rooms-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
    max-height: 400px;
    overflow-y: auto;
    padding-right: 8px;
}

.room-card {
    background: white;
    border: 2px solid var(--border);
    border-radius: 12px;
    padding: 16px;
    transition: all 0.2s;
}

.room-card.available {
    border-color: #d1fae5;
}

.room-card.occupied {
    border-color: #fee2e2;
    opacity: 0.7;
}

.room-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.room-number {
    font-size: 24px;
    font-weight: 800;
    color: var(--text-main);
}

.room-type {
    font-size: 11px;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 20px;
    background: #e0f2fe;
    color: var(--primary);
}

.room-details {
    margin-bottom: 16px;
}

.room-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    font-size: 13px;
}

.room-bed {
    color: var(--text-muted);
}

.room-status {
    font-size: 11px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 20px;
}

.room-status.available {
    background: #d1fae5;
    color: #065f46;
}

.room-status.occupied {
    background: #fee2e2;
    color: #b91c1c;
}

.room-equipment {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 8px;
}

.equipment-tag {
    font-size: 10px;
    padding: 2px 6px;
    background: #f3f4f6;
    border-radius: 4px;
    color: var(--text-muted);
}

.occupied-info {
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px dashed #f1f5f9;
    font-size: 11px;
    color: var(--text-muted);
}

.patient-in-room, .occupied-time {
    display: block;
    margin-bottom: 2px;
}

.assign-btn {
    width: 100%;
    padding: 10px;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
}

.assign-btn:hover:not(:disabled) {
    background: var(--primary-dark);
    transform: translateY(-1px);
}

.assign-btn.disabled {
    background: #e5e7eb;
    color: #9ca3af;
    cursor: not-allowed;
}

/* Filter Tabs */
.filter-tabs {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 8px 16px;
    background: #f1f5f9;
    border: none;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.2s;
}

.filter-tab.active {
    background: var(--primary);
    color: white;
}

.filter-tab:hover:not(.active) {
    background: #e2e8f0;
}

/* Quick Actions */
.quick-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.quick-btn {
    flex: 1;
    padding: 12px 16px;
    background: white;
    border: 1px solid var(--border);
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-main);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
}

.quick-btn:hover {
    border-color: var(--primary);
    background: #f0f9ff;
    transform: translateY(-1px);
}

/* Recent Activity */
.recent-activity {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.activity-item {
    display: flex;
    align-items: center;
    padding: 16px;
    background: #f8fafc;
    border-radius: 10px;
    border-left: 4px solid var(--primary);
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #e0f2fe;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    margin-right: 16px;
    flex-shrink: 0;
}

.activity-details {
    flex: 1;
}

.activity-details p {
    font-weight: 500;
    margin-bottom: 4px;
}

.activity-time {
    font-size: 12px;
    color: var(--text-muted);
}

.activity-room {
    font-size: 13px;
}

.room-tag {
    background: var(--primary);
    color: white;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
}

/* Room Stats */
.room-stats {
    display: flex;
    gap: 8px;
}

.stat-tag {
    font-size: 11px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 20px;
}

.stat-tag.available {
    background: #d1fae5;
    color: #065f46;
}

.stat-tag.occupied {
    background: #fee2e2;
    color: #b91c1c;
}

/* Badge */
.badge {
    font-size: 12px;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
}

.badge-primary {
    background: #dbeafe;
    color: var(--primary);
}

/* Form Actions */
.btn-secondary {
    padding: 10px 20px;
    background: #f1f5f9;
    border: 1px solid var(--border);
    border-radius: 10px;
    color: var(--text-main);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-secondary:hover {
    background: #e2e8f0;
}

/* Scrollbar Styling */
.rooms-grid::-webkit-scrollbar {
    width: 6px;
}

.rooms-grid::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.rooms-grid::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.rooms-grid::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Responsive */
@media (max-width: 1200px) {
    .vitals-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .rooms-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .vitals-grid {
        grid-template-columns: 1fr;
    }
    
    .rooms-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        flex-direction: column;
    }
    
    .activity-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .activity-room {
        margin-top: 8px;
    }
}
</style>

<script>
// Sample static functionality
function clearPatientSelection() {
    document.getElementById('selectedPatientInfo').style.display = 'none';
    document.getElementById('activePatientBadge').style.display = 'none';
}

function assignToRoom(roomNumber) {
    if (document.getElementById('selectedPatientInfo').style.display === 'none') {
        alert('Please select a patient first');
        return;
    }
    
    const vitals = {
        temp: document.getElementById('temperature').value || 'N/A',
        bp: document.getElementById('bloodPressure').value || 'N/A',
        hr: document.getElementById('heartRate').value || 'N/A'
    };
    
    alert(`Patient assigned to Room ${roomNumber}\nVitals recorded:\nTemperature: ${vitals.temp}\nBlood Pressure: ${vitals.bp}\nHeart Rate: ${vitals.hr}`);
    
    // Reset form
    document.getElementById('vitalsForm').reset();
    clearPatientSelection();
}

function assignEmergencyRoom() {
    alert('Assigning to nearest available emergency room...');
    assignToRoom(301); // Sample emergency room
}

function assignICU() {
    alert('Assigning to ICU with priority...');
    assignToRoom(201); // Sample ICU room
}

function assignIsolation() {
    alert('Assigning to isolation room...');
    assignToRoom(205); // Sample isolation room
}

// Simulate patient search selection
document.getElementById('patientSearch').addEventListener('focus', function() {
    // In real app, this would show search results
    setTimeout(() => {
        document.getElementById('selectedPatientInfo').style.display = 'block';
        document.getElementById('activePatientBadge').style.display = 'inline-flex';
    }, 500);
});

// Room filtering
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        // Remove active class from all tabs
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        // Add active class to clicked tab
        this.classList.add('active');
        
        const filter = this.dataset.filter;
        const rooms = document.querySelectorAll('.room-card');
        
        rooms.forEach(room => {
            if (filter === 'all' || room.dataset.type === filter) {
                room.style.display = 'block';
            } else {
                room.style.display = 'none';
            }
        });
    });
});

// Vitals validation and status update
document.querySelectorAll('.vitals-grid input').forEach(input => {
    input.addEventListener('input', function() {
        const id = this.id;
        const value = this.value;
        const statusElement = document.getElementById(id.replace(/[A-Z]/g, match => match.toLowerCase()) + 'Status');
        
        if (!value) {
            statusElement.textContent = 'Not recorded';
            statusElement.style.background = '#e5e7eb';
            return;
        }
        
        // Sample validation logic
        if (id === 'temperature') {
            const temp = parseFloat(value);
            if (temp < 95) statusElement.textContent = 'Low';
            else if (temp > 100.4) statusElement.textContent = 'High';
            else statusElement.textContent = 'Normal';
        } else if (id === 'heartRate') {
            const hr = parseInt(value);
            if (hr < 60) statusElement.textContent = 'Low';
            else if (hr > 100) statusElement.textContent = 'High';
            else statusElement.textContent = 'Normal';
        }
        
        // Update status color based on text
        const statusText = statusElement.textContent.toLowerCase();
        if (statusText === 'normal') {
            statusElement.style.background = '#10b981';
        } else if (statusText === 'high' || statusText === 'low') {
            statusElement.style.background = '#ef4444';
        } else {
            statusElement.style.background = '#f59e0b';
        }
    });
});

// Save vitals button
document.getElementById('saveVitalsBtn').addEventListener('click', function() {
    const patientSelected = document.getElementById('selectedPatientInfo').style.display !== 'none';
    
    if (!patientSelected) {
        alert('Please select a patient first');
        return;
    }
    
    const hasVitals = Array.from(document.querySelectorAll('.vitals-grid input')).some(input => input.value);
    
    if (!hasVitals) {
        alert('Please record at least one vital sign');
        return;
    }
    
    alert('Vitals saved successfully! Now assign the patient to a room.');
});

// Quick admit button
document.getElementById('quickAdmitBtn').addEventListener('click', function() {
    document.getElementById('selectedPatientInfo').style.display = 'block';
    document.getElementById('activePatientBadge').style.display = 'inline-flex';
    
    // Auto-fill sample vitals for quick admit
    document.getElementById('temperature').value = '98.6';
    document.getElementById('bloodPressure').value = '120/80';
    document.getElementById('heartRate').value = '72';
    document.getElementById('respiratoryRate').value = '16';
    document.getElementById('oxygenSaturation').value = '98';
    document.getElementById('bloodGlucose').value = '100';
    
    // Trigger input events to update status
    document.querySelectorAll('.vitals-grid input').forEach(input => {
        input.dispatchEvent(new Event('input'));
    });
});
</script>