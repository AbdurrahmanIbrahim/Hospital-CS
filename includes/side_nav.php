  <aside class="sidebar no-print">
        <div class="sidebar-header">
            <div class="logo-box">H</div>
            <span class="brand-name">Garima Standard Hospital</span>
        </div>

        <nav class="sidebar-nav">
            <a href="<?=ROOT_URL?>" class="static-nav-item <?=$location=='dashboard' ? 'active-link' : ''?>">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="nav-text">Dashboard</span>
            </a>

            <div class="nav-group">
             <?php if($_SESSION['type'] == 0){
                 ?>
                <details>
                    <summary class="<?=$location=='admin' ? 'active-link' : ''?>">
                        <div style="display: flex; align-items: center;">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <span class="nav-text">Administrator</span>
                        </div>
                        <svg class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </summary>
                    <div class="dropdown-content">
                        <a href="<?=ROOT_URL?>settings/" class="dropdown-item">Hospital Settings</a>
                        <a href="<?=ROOT_URL?>staff/" class="dropdown-item">New Staff</a>
                        <a href="<?=ROOT_URL?>staff/view.php" class="dropdown-item">View Staff</a>
                    </div>
                </details>
             <?php } ?>


             <?php if($_SESSION['type'] == 0 OR $_SESSION['type']==5){
                 ?>
                       <details>
                    <summary  class="<?=$location=='patient' ? 'active-link' : ''?>">
                        <div style="display: flex; align-items: center;">
                              <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <span class="nav-text">Patient Management</span>
                        </div>
                        <svg class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </summary>
                    <div class="dropdown-content">
                        <a href="<?=ROOT_URL?>file_types/" class="dropdown-item">Add File Type</a>
                        <a href="<?=ROOT_URL?>file_types/view.php" class="dropdown-item">View File Types</a>
                        <a href="<?=ROOT_URL?>schemes/" class="dropdown-item">Add Scheme</a>
                        <a href="<?=ROOT_URL?>schemes/view.php" class="dropdown-item">View Schemes</a>
                        <a href="<?=ROOT_URL?>family/" class="dropdown-item">Add Family</a>
                         <a href="<?=ROOT_URL?>family/view.php" class="dropdown-item">View Family</a>
                        <a href="<?=ROOT_URL?>patient/" class="dropdown-item">Add Patient</a>
                        <a href="<?=ROOT_URL?>patient/view.php" class="dropdown-item">View Patients</a>
                    </div>
                </details>
                <details>
    <summary class="<?=$location=='rooms' ? 'active-link' : ''?>">
        <div style="display: flex; align-items: center;">
            <!-- Room / Ward Icon -->
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="7" width="18" height="10" rx="2"></rect>
                <line x1="7" y1="17" x2="7" y2="21"></line>
                <line x1="17" y1="17" x2="17" y2="21"></line>
                <line x1="3" y1="12" x2="21" y2="12"></line>
            </svg>
            <span class="nav-text">Room Management</span>
        </div>

        <svg class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 9l-7 7-7-7"></path>
        </svg>
    </summary>

    <div class="dropdown-content">
        <a href="<?=ROOT_URL?>rooms/" class="dropdown-item">Add Room</a>
        <a href="<?=ROOT_URL?>rooms/view.php" class="dropdown-item">View Rooms</a>
        <a href="<?=ROOT_URL?>rooms/assign_doctors.php" class="dropdown-item">Room Assignments</a>
    </div>
</details>
 <details>
    <summary class="<?=$location=='ward' ? 'active-link' : ''?>">
        <div style="display: flex; align-items: center; gap: 10px;">
            <!-- Ward / Hospital Icon -->
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                <line x1="12" y1="7" x2="12" y2="17"></line>
                <line x1="7" y1="12" x2="17" y2="12"></line>
            </svg>

            <span class="nav-text">Ward Management</span>
        </div>

        <svg class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 9l-7 7-7-7"></path>
        </svg>
    </summary>

    <div class="dropdown-content">
        <a href="<?=ROOT_URL?>ward/" class="dropdown-item">Add Ward</a>
        <a href="<?=ROOT_URL?>ward/view.php" class="dropdown-item">View Ward</a>
    </div>
</details>



             <?php } ?>

          
             <?php if($_SESSION['type'] == 0 OR $_SESSION['type']==5){
                 ?>
                       <details>
                    <summary  class="<?=$location=='appointments' ? 'active-link' : ''?>">
                        <div style="display: flex; align-items: center;">
                              <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" 
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="24" height="24">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
                            <span class="nav-text">Appointments</span>
                        </div>
                        <svg class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 9l-7 7-7-7"></path>
        </svg>
                    </summary>
                    <div class="dropdown-content">
                        <a href="<?=ROOT_URL?>patient/view.php" class="dropdown-item">Book Appointment</a>
                        <a href="<?=ROOT_URL?>appointments/index.php" class="dropdown-item">View Appointments</a>
                    </div>
                </details>
             <?php } ?>

             <?php if($_SESSION['type'] == 0 OR $_SESSION['type'] == 4){
                ?>
                   <details>
                    <summary class="<?=$location=='nurses' ? 'active-link' : ''?>">
                        <div style="display: flex; align-items: center;">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            <span class="nav-text">Nurse Desk</span>
                        </div>
                        <svg class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </summary>
                    <div class="dropdown-content">
                        <a href="<?=ROOT_URL?>vitals/" class="dropdown-item">Add New Vital</a>
                        <a href="<?=ROOT_URL?>vitals/view.php" class="dropdown-item">View Vitals</a>
                        <a href="<?=ROOT_URL?>vitals/record_vital.php" class="dropdown-item">Record Patient Vital</a>
                    </div>
                </details>
             <?php } ?>

             <?php if($_SESSION['type'] == 0 OR $_SESSION['type'] == 3 OR $_SESSION['type'] == 4){ ?>
                <details>
                    <summary class="<?=$location=='admission' ? 'active-link' : ''?>">
                        <div style="display: flex; align-items: center;">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z"></path>
                                <path d="M9 16h6M12 13v6"></path>
                            </svg>
                            <span class="nav-text">Admissions</span>
                        </div>
                        <svg class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>
                    <div class="dropdown-content">
                        <a href="<?=ROOT_URL?>admission/view.php" class="dropdown-item">Active Admissions</a>
                        <a href="<?=ROOT_URL?>admission/view.php?status=1" class="dropdown-item">Discharged</a>
                        <a href="<?=ROOT_URL?>admission/view.php?status=all" class="dropdown-item">All Admissions</a>
                    </div>
                </details>
             <?php } ?>

                  <?php if($_SESSION['type'] == 0 OR $_SESSION['type'] == 7){
                ?>
               <details>
    <summary class="<?=$location=='payments' ? 'active-link' : ''?>">
        <div style="display: flex; align-items: center;">

            <!-- Payments / Billing Icon -->
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 14h6M9 10h6M5 4h14a2 2 0 012 2v14l-3-2-3 2-3-2-3 2-3-2-3 2V6a2 2 0 012-2z" />
            </svg>

            <span class="nav-text">Payments / Billing</span>
        </div>

        <!-- Dropdown Chevron -->
        <svg class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 9l-7 7-7-7"></path>
        </svg>
    </summary>

    <div class="dropdown-content">
        <a href="<?=ROOT_URL?>payments/?status=0" class="dropdown-item">Pending</a>
        <a href="<?=ROOT_URL?>payments/?status=1" class="dropdown-item">Paid</a>
        <a href="<?=ROOT_URL?>payments/?status=-1" class="dropdown-item">Rejected</a>
        <a href="<?=ROOT_URL?>payments/view.php" class="dropdown-item">Record</a>
    </div>
</details>

             <?php } ?>
    
    
             

               
             <?php if($_SESSION['type'] == 0 OR $_SESSION['type'] == 6){
                ?>
                             <details>
                <summary class="<?=$location=='pharmacy' ? 'active-link' : ''?>">
                    <div style="display: flex; align-items: center;">
                        <!-- Pharmacy Icon -->
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 3h6a2 2 0 012 2v3h3a2 2 0 012 2v6a2 2 0 01-2 2h-3v3a2 2 0 01-2 2H9a2 2 0 01-2-2v-3H4a2 2 0 01-2-2v-6a2 2 0 012-2h3V5a2 2 0 012-2z"/>
                        </svg>
                        <span class="nav-text">Pharmacy</span>
                    </div>
                    <svg class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 9l-7 7-7-7"></path>
                    </svg>
                </summary>

                <div class="dropdown-content">
                    <a href="<?=ROOT_URL?>categories/" class="dropdown-item">Drug Categories</a>
                    <a href="<?=ROOT_URL?>drugs/" class="dropdown-item">Add New Drug</a>
                    <a href="<?=ROOT_URL?>drugs/view.php" class="dropdown-item">Drug List</a>

                    <a href="<?=ROOT_URL?>stock/" class="dropdown-item">Stock Entry</a>
                    <a href="<?=ROOT_URL?>stock/view.php" class="dropdown-item">Stock Levels</a>
                    <a href="<?=ROOT_URL?>stock/report.php" class="dropdown-item">Stock Reports</a>

                    <a href="<?=ROOT_URL?>prescriptions/" class="dropdown-item">Prescriptions</a>
                    <a href="<?=ROOT_URL?>prescriptions/dispensed.php" class="dropdown-item">Dispensed Drugs Report</a>

                    <a href="<?=ROOT_URL?>pharmacy-pos/" class="dropdown-item">POS - Drug Sales</a>
                    <a href="<?=ROOT_URL?>pharmacy-pos/sales_history.php" class="dropdown-item">POS Sales History</a>
                </div>
            </details>
             <?php } ?>
               
           <?php if($_SESSION['type'] == 0 OR $_SESSION['type'] == 2){
              ?>
                 <details>
                    <summary class="<?=$location=='lab' ? 'active-link' : ''?>">
                        <div style="display: flex; align-items: center;">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                            <span class="nav-text">Laboratory</span>
                        </div>
                        <svg class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </summary>
                    <div class="dropdown-content">
                        <a href="<?=ROOT_URL?>specimen/" class="dropdown-item">Register Specimen</a>
                        <a href="<?=ROOT_URL?>specimen/view.php" class="dropdown-item">Specimen List</a>
                        <a href="<?=ROOT_URL?>test/" class="dropdown-item">Create Laboratory Test</a>
                        <a href="<?=ROOT_URL?>test/view.php" class="dropdown-item">Laboratory Tests</a>
                        <a href="<?=ROOT_URL?>lab/index.php?status=1" class="dropdown-item">Specimen Collection</a>
                        <a href="<?=ROOT_URL?>lab/index.php?status=2" class="dropdown-item">Acknowledge Collection</a>
                        <a href="<?=ROOT_URL?>lab/index.php?status=3" class="dropdown-item">Result Preparation</a>
                        <a href="<?=ROOT_URL?>lab/index.php?status=4" class="dropdown-item">Results Verification</a>
                        <a href="<?=ROOT_URL?>lab/index.php?status=5" class="dropdown-item">Print Result</a>
                        <a href="<?=ROOT_URL?>lab/index.php" class="dropdown-item">Lab Tracker</a>
                        <a href="<?=ROOT_URL?>lab-pos/" class="dropdown-item">POS - Direct Lab Testing</a>
                        <a href="<?=ROOT_URL?>lab-pos/history.php" class="dropdown-item">POS Test History</a>
                   </div>

                </details>
           <?php } ?>

           <?php if($_SESSION['type'] == 0 OR $_SESSION['type'] == 9){ ?>
                <details>
                    <summary class="<?=$location=='radiology' ? 'active-link' : ''?>">
                        <div style="display: flex; align-items: center;">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M12 16v-4"></path>
                                <path d="M12 8h.01"></path>
                            </svg>
                            <span class="nav-text">Radiology</span>
                        </div>
                        <svg class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </summary>
                    <div class="dropdown-content">
                        <a href="<?=ROOT_URL?>scans/" class="dropdown-item">Add Scan Type</a>
                        <a href="<?=ROOT_URL?>scans/view.php" class="dropdown-item">View Scan Types</a>
                        <a href="<?=ROOT_URL?>radiology/index.php?status=1" class="dropdown-item">Pending Scans</a>
                        <a href="<?=ROOT_URL?>radiology/index.php?status=2" class="dropdown-item">Perform Scan</a>
                        <a href="<?=ROOT_URL?>radiology/index.php?status=3" class="dropdown-item">Upload Report</a>
                        <a href="<?=ROOT_URL?>radiology/index.php?status=4" class="dropdown-item">Verify Report</a>
                        <a href="<?=ROOT_URL?>radiology/index.php?status=5" class="dropdown-item">Print / Release</a>
                        <a href="<?=ROOT_URL?>radiology/index.php" class="dropdown-item">Scan Tracker</a>
                        <a href="<?=ROOT_URL?>radiology-pos/" class="dropdown-item">POS - Walk-in Scans</a>
                    </div>
                </details>
           <?php } ?>

            </div>

            <div style="margin-top: 20px; border-top: 1px solid #1e293b; padding-top: 20px;">
                <a href="<?=ROOT_URL?>profile/index.php" class="static-nav-item <?=$location=='profile' ? 'active-link' : ''?>">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span class="nav-text">Profile</span>
                </a>
                <a href="<?=ROOT_URL?>logout.php" class="static-nav-item logout-item">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    <span class="nav-text">Logout</span>
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <img style="width: 80px;height: auto;"src="<?=!empty($_SESSION['pic']) ? ROOT_URL.'images/dp/'.$_SESSION['pic'] : ROOT_URL.'images/dp/default.jpg' ?>">
            <div class="user-info">
                <p><?=!empty($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Nill'?></p>
                <span><?=getUserType($_SESSION['type'])?></span>
            </div>
        </div>
    </aside>