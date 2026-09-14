<?php
// public/apply.php
require_once '../config/database.php';

$animal_id = $_GET['pet_id'] ?? '';
if (!$animal_id) {
    header('Location: index.php');
    exit();
}

// Get pet details
$stmt = $pdo->prepare("SELECT * FROM pets WHERE animal_id = ?");
$stmt->execute([$animal_id]);
$pet = $stmt->fetch();

if (!$pet || $pet['adoption_status'] !== 'Available') {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply to Adopt <?= htmlspecialchars($pet['pet_name']) ?> - Adoption Pawtal</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- Branding & Shared Styles --- */
        :root {
            --brand-gradient: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%);
            --primary-color: #FF6B6B; 
            --secondary-color: #FF8E53;
            --text-dark: #2d3436;
            --text-muted: #636e72;
            --bg-soft: #fff9f5;
            --paw-pattern: url("data:image/svg+xml,%3Csvg width='50' height='70' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill='%23FF6B6B' fill-opacity='0.10' d='M22.6,30.1c-5.2,0-9.4-4.2-9.4-9.4s4.2-9.4,9.4-9.4s9.4,4.2,9.4,9.4S27.8,30.1,22.6,30.1z M48.9,21.4 c-5.2,0-9.4-4.2-9.4-9.4s4.2-9.4,9.4-9.4s9.4,4.2,9.4,9.4S54.1,21.4,48.9,21.4z M75.6,30.1c-5.2,0-9.4-4.2-9.4-9.4s4.2-9.4,9.4-9.4 s9.4,4.2,9.4,9.4S80.8,30.1,75.6,30.1z M49.2,33.9c-18.6,0-26.9,14.4-28.1,23.9c-1.4,11.1,13,19.8,28.1,19.8s29.4-8.7,28.1-19.8 C76.1,48.3,67.8,33.9,49.2,33.9z'/%3E%3C/svg%3E");
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--bg-soft);
            background-image: var(--paw-pattern);
            color: var(--text-dark);
            padding-top: 80px;
        }

        /* Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            padding: 1rem 0;
        }
        .navbar-brand {
            font-weight: 800;
            font-size: 1.8rem;
            background: var(--brand-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .nav-link { font-weight: 700; color: var(--text-dark) !important; margin: 0 10px; transition: color 0.3s; }
        .nav-link:hover { color: var(--primary-color) !important; }
        .btn-admin { background-color: #f1f2f6; border-radius: 50px; padding: 8px 20px; color: var(--text-muted) !important; }

        /* Form Container */
        .application-card {
            background: white;
            border-radius: 25px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.05);
            padding: 3rem;
            border: 1px solid rgba(0,0,0,0.02);
            margin-bottom: 3rem;
        }

        .form-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .pet-summary {
            display: inline-block;
            background: linear-gradient(135deg, #fff0e6 0%, #fff 100%);
            padding: 15px 30px;
            border-radius: 50px;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid #ffe0d1;
        }

        .section-title {
            color: var(--text-dark);
            font-weight: 800;
            font-size: 1.3rem;
            margin-top: 2.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f2f6;
        }

        .section-title i {
            background: var(--brand-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-right: 12px;
            font-size: 1.4rem;
        }

        /* Inputs */
        .form-label {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border: 2px solid #f1f2f6;
            border-radius: 12px;
            padding: 12px 15px;
            font-weight: 600;
            transition: all 0.3s;
            background-color: #fcfcfc;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 4px rgba(255, 142, 83, 0.1);
            background-color: white;
        }

        .required-star { color: var(--primary-color); margin-left: 3px; }

        /* Buttons */
        .btn-gradient {
            background: var(--brand-gradient);
            border: none;
            color: white;
            border-radius: 15px;
            padding: 15px 40px;
            font-weight: 800;
            font-size: 1.1rem;
            box-shadow: 0 10px 25px rgba(255, 107, 107, 0.3);
            transition: all 0.3s;
            width: 100%;
        }

        .btn-gradient:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(255, 107, 107, 0.4);
            color: white;
        }

        .btn-outline-cancel {
            border: 2px solid #f1f2f6;
            color: var(--text-muted);
            border-radius: 15px;
            padding: 15px 40px;
            font-weight: 700;
            background: transparent;
            width: 100%;
            display: block;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-outline-cancel:hover {
            background: #f1f2f6;
            color: var(--text-dark);
        }

        /* Checkboxes */
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Footer */
        footer {
            background: white;
            color: var(--text-dark);
            padding-top: 4rem;
            margin-top: 5rem;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        .footer-title {
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: var(--brand-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .footer-links a { color: var(--text-muted); text-decoration: none; margin-bottom: 5px; display: block; }
        .copyright { background: #f8f9fa; padding: 1.5rem 0; margin-top: 3rem; color: var(--text-muted); }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-paw me-2"></i>Adoption Pawtal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="index.php">Browse Pets</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#contact">Contact</a></li>
                    <li class="nav-item ms-lg-3">
                        <a class="nav-link btn-admin" href="../admin/login.php">
                            <small><i class="fas fa-lock me-1"></i> Admin</small>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <!-- Form Header -->
                <div class="form-header">
                    <div class="pet-summary">
                        <span class="fw-bold text-dark" style="font-size: 1.1rem;">
                            <i class="fas fa-heart text-danger me-2"></i>
                            Applying for: <?= htmlspecialchars($pet['pet_name']) ?> 
                            <span class="text-muted ms-1 fw-normal">(<?= htmlspecialchars($pet['breed']) ?>)</span>
                        </span>
                    </div>
                    <h1 class="fw-bolder mb-2">Adoption Application</h1>
                    <p class="text-muted">Please fill out the form below to start your journey.</p>
                </div>

                <form method="POST" action="submit-application.php" class="application-card needs-validation" novalidate>
                    <input type="hidden" name="animal_id" value="<?= htmlspecialchars($animal_id) ?>">
                    
                    <div class="text-end mb-4">
                        <small class="text-muted"><span class="required-star">*</span> Indicates required fields</small>
                    </div>

                    <!-- 1. Personal Information -->
                    <h3 class="section-title"><i class="fas fa-user-circle"></i> Personal Information</h3>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name <span class="required-star">*</span></label>
                            <input type="text" name="first_name" class="form-control" required>
                            <div class="invalid-feedback">Please enter your first name.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name <span class="required-star">*</span></label>
                            <input type="text" name="last_name" class="form-control" required>
                            <div class="invalid-feedback">Please enter your last name.</div>
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Email Address <span class="required-star">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number <span class="required-star">*</span></label>
                            <input type="tel" name="phone" class="form-control" placeholder="(123) 456-7890" required>
                            <div class="invalid-feedback">Please enter your phone number.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Street Address <span class="required-star">*</span></label>
                        <input type="text" name="address" class="form-control" placeholder="123 Main Street" required>
                        <div class="invalid-feedback">Please enter your address.</div>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">City <span class="required-star">*</span></label>
                            <input type="text" name="city" class="form-control" required>
                            <div class="invalid-feedback">Please enter your city.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Postal Code <span class="required-star">*</span></label>
                            <input type="text" name="postal_code" class="form-control" required>
                            <div class="invalid-feedback">Please enter your postal code.</div>
                        </div>
                    </div>

                    <!-- 2. Living Situation -->
                    <h3 class="section-title"><i class="fas fa-home"></i> Living Situation</h3>
                    
                    <div class="mb-3">
                        <label class="form-label">Household Type <span class="required-star">*</span></label>
                        <select name="household_type" class="form-select" required>
                            <option value="">Select Type...</option>
                            <option value="House">House</option>
                            <option value="Apartment">Apartment</option>
                            <option value="Condo">Condo</option>
                            <option value="Townhouse">Townhouse</option>
                            <option value="Other">Other</option>
                        </select>
                        <div class="invalid-feedback">Please select your household type.</div>
                    </div>

                    <!-- 3. Household Details -->
                    <h3 class="section-title"><i class="fas fa-users"></i> Household Details</h3>
                    
                    <div class="mb-3">
                        <label class="form-label">Do you currently have other pets? <span class="required-star">*</span></label>
                        <select name="has_other_pets" class="form-select" required onchange="toggleOtherPets(this)">
                            <option value="">Select...</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                        <div class="invalid-feedback">Please select an option.</div>
                    </div>
                    
                    <div class="mb-3 bg-light p-3 rounded-3" id="other_pets_details_div" style="display:none;">
                        <label class="form-label">Please describe your other pets (type, age, temperament)</label>
                        <textarea name="other_pets_details" class="form-control" rows="3" placeholder="Example: 1 cat, 3 years old, very friendly and playful"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Do you have children? <span class="required-star">*</span></label>
                        <select name="has_children" class="form-select" required onchange="toggleChildren(this)">
                            <option value="">Select...</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                        <div class="invalid-feedback">Please select an option.</div>
                    </div>
                    
                    <div class="mb-3 bg-light p-3 rounded-3" id="children_ages_div" style="display:none;">
                        <label class="form-label">Ages of children</label>
                        <input type="text" name="children_ages" class="form-control" placeholder="Example: 5, 8, 12">
                        <small class="text-muted">Separate ages with commas</small>
                    </div>

                    <!-- 4. Experience & Preferences -->
                    <h3 class="section-title"><i class="fas fa-paw"></i> Experience & Preferences</h3>
                    
                    <div class="mb-3">
                        <label class="form-label">Pet Ownership Experience Level <span class="required-star">*</span></label>
                        <select name="experience_level" class="form-select" required>
                            <option value="">Select...</option>
                            <option value="First-time">First-time pet owner</option>
                            <option value="Some Experience">Some experience with pets</option>
                            <option value="Experienced">Experienced pet owner</option>
                        </select>
                        <div class="invalid-feedback">Please select your experience level.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Why do you want to adopt <?= htmlspecialchars($pet['pet_name']) ?>? <span class="required-star">*</span></label>
                        <textarea name="reason_for_adoption" class="form-control" rows="5" placeholder="Tell us about why you'd like to adopt this pet, your lifestyle, and how the pet would fit into your home..." required></textarea>
                        <div class="invalid-feedback">Please share your reason for adoption.</div>
                    </div>

                    <!-- 5. References -->
                    <h3 class="section-title"><i class="fas fa-user-friends"></i> References</h3>
                    <p class="text-muted small mb-4">Please provide two personal references (not family members).</p>
                    
                    <div class="bg-light p-4 rounded-4 mb-4">
                        <h6 class="fw-bold text-primary mb-3">Reference 1</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Name</label>
                                <input type="text" name="reference1_name" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="reference1_phone" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Relationship</label>
                                <input type="text" name="reference1_relationship" class="form-control" placeholder="e.g., Friend, Neighbor" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-light p-4 rounded-4 mb-3">
                        <h6 class="fw-bold text-primary mb-3">Reference 2</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Name</label>
                                <input type="text" name="reference2_name" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="reference2_phone" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Relationship</label>
                                <input type="text" name="reference2_relationship" class="form-control" placeholder="e.g., Colleague" required>
                            </div>
                        </div>
                    </div>

                    <!-- 6. Agreement -->
                    <h3 class="section-title"><i class="fas fa-file-signature"></i> Agreement</h3>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="agree1" required>
                        <label class="form-check-label" for="agree1">
                            I agree to the terms and conditions of the adoption process <span class="required-star">*</span>
                        </label>
                        <div class="invalid-feedback">You must agree to continue.</div>
                    </div>
                    
                    <div class="form-check mb-5">
                        <input class="form-check-input" type="checkbox" id="agree2" required>
                        <label class="form-check-label" for="agree2">
                            I understand that this is an application and does not guarantee adoption <span class="required-star">*</span>
                        </label>
                        <div class="invalid-feedback">You must agree to continue.</div>
                    </div>

                    <!-- Actions -->
                    <div class="d-grid gap-3">
                        <button type="submit" class="btn btn-gradient btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Submit Application
                        </button>
                        <a href="pet-details.php?id=<?= htmlspecialchars($animal_id) ?>" class="btn btn-outline-cancel">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="footer-title"><i class="fas fa-paw me-2"></i>Adoption Pawtal</h5>
                    <p class="text-muted">We are dedicated to connecting loving families with pets in need. Every adoption saves a life and brings joy to a family.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="footer-title">Get in Touch</h5>
                    <ul class="list-unstyled text-muted">
                        <li class="mb-2"><i class="fas fa-phone me-2 text-danger"></i> (123) 456-7890</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2 text-danger"></i> info@adoptionpawtal.com</li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="footer-title">Quick Links</h5>
                    <div class="footer-links">
                        <a href="index.php">Browse Pets</a>
                        <a href="#">Adoption Process</a>
                        <a href="#">Donate</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="copyright text-center">
            <div class="container">
                <p class="mb-0">&copy; 2025 Adoption Pawtal. Made with <i class="fas fa-heart text-danger mx-1"></i> for pets.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleOtherPets(select) {
        const detailsDiv = document.getElementById('other_pets_details_div');
        const textarea = detailsDiv.querySelector('textarea');
        if (select.value === '1') {
            detailsDiv.style.display = 'block';
            textarea.required = true;
        } else {
            detailsDiv.style.display = 'none';
            textarea.required = false;
            textarea.value = '';
        }
    }
    
    function toggleChildren(select) {
        const agesDiv = document.getElementById('children_ages_div');
        const input = agesDiv.querySelector('input');
        if (select.value === '1') {
            agesDiv.style.display = 'block';
            input.required = true;
        } else {
            agesDiv.style.display = 'none';
            input.required = false;
            input.value = '';
        }
    }

    // Bootstrap form validation
    (function() {
        'use strict';
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
    </script>
</body>
</html>