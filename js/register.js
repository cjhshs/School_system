/**
 * Registration Form JavaScript
 * Handles profile picture upload, AJAX calls for cascading dropdowns
 */

// Profile Picture Upload with Drag & Drop
document.addEventListener('DOMContentLoaded', function () {
    initProfilePicture();
    initRegionDropdown();
    initCourseMajorDropdown();
});

function initProfilePicture() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('profile_picture');
    const previewImage = document.getElementById('previewImage');
    const dropZoneText = document.getElementById('dropZoneText');

    if (!dropZone || !fileInput) {
        console.log('Drop zone or file input not found');
        return;
    }

    // Click to browse
    dropZone.addEventListener('click', function () {
        fileInput.click();
    });

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Highlight on drag
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('dragover');
        }, false);
    });

    // Handle dropped files
    dropZone.addEventListener('drop', function (e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        if (files.length > 0) {
            fileInput.files = files;
            handleFileSelect(files[0]);
        }
    });

    // File input change
    fileInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            handleFileSelect(this.files[0]);
        }
    });

    function handleFileSelect(file) {
        // Validate file type
        if (!file.type.match('image.*')) {
            alert('Please select an image file (JPG, PNG, GIF, etc.)');
            return;
        }

        // Validate file size (5MB max)
        if (file.size > 5000000) {
            alert('File size must be less than 5MB');
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            previewImage.src = e.target.result;
            previewImage.style.display = 'block';
            previewImage.style.width = '150px';
            previewImage.style.height = '150px';
            previewImage.style.objectFit = 'cover';
            previewImage.style.borderRadius = '50%';
            if (dropZoneText) {
                dropZoneText.style.display = 'none';
            }
        };
        reader.readAsDataURL(file);
    }
}

// Region/Province/City/Barangay cascading dropdown
function initRegionDropdown() {
    const regionSelect = document.getElementById('region');
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const barangaySelect = document.getElementById('barangay');
    const zipcodeInput = document.getElementById('zipcode');

    if (!regionSelect) return;

    // Region change -> Load Provinces
    regionSelect.addEventListener('change', function () {
        const region = this.value;

        if (region) {
            loadProvinces(region);
        } else {
            if (provinceSelect) provinceSelect.innerHTML = '<option value="">Select Region First</option>';
            if (citySelect) citySelect.innerHTML = '<option value="">Select Province First</option>';
            if (barangaySelect) barangaySelect.innerHTML = '<option value="">Select City First</option>';
            if (zipcodeInput) zipcodeInput.value = '';
        }
    });

    // Province change -> Load Cities
    if (provinceSelect) {
        provinceSelect.addEventListener('change', function () {
            const province = this.value;

            if (province) {
                loadCities(province);
            } else {
                if (citySelect) citySelect.innerHTML = '<option value="">Select Province First</option>';
                if (barangaySelect) barangaySelect.innerHTML = '<option value="">Select City First</option>';
                if (zipcodeInput) zipcodeInput.value = '';
            }
        });
    }

    // Update complete address
    function updateCompleteAddress() {
        const address = document.getElementById('address')?.value || '';
        const barangay = document.getElementById('barangay')?.value || '';
        const city = document.getElementById('city')?.value || '';
        const province = document.getElementById('province')?.value || '';
        const region = document.getElementById('region')?.value || '';
        const zipcode = document.getElementById('zipcode')?.value || '';

        const parts = [];
        if (region) parts.push(region);
        if (province) parts.push(province);
         if (city) parts.push(city);
        if (barangay) parts.push('Brgy. ' + barangay);
        if (address) parts.push(address);
        if (zipcode) parts.push(zipcode);

        const completeAddress = parts.join(', ');
        const completeInput = document.getElementById('complete_address');
        if (completeInput) {
            completeInput.value = completeAddress;
        }
    }

    // Add event listeners for complete address
    const addressFields = ['region', 'province', 'city', 'barangay', 'address', 'zipcode'];
    addressFields.forEach(function (fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('change', updateCompleteAddress);
            field.addEventListener('input', updateCompleteAddress);
        }
    });

    // City change -> Load Barangays and Zipcode
    if (citySelect) {
        citySelect.addEventListener('change', function () {
            const city = this.value;
            const selectedOption = this.options[this.selectedIndex];
            const zipcode = selectedOption ? selectedOption.getAttribute('data-zipcode') : '';

            if (city) {
                loadBarangays(city);
                if (zipcodeInput) zipcodeInput.value = zipcode || '';
            } else {
                if (barangaySelect) barangaySelect.innerHTML = '<option value="">Select City First</option>';
                if (zipcodeInput) zipcodeInput.value = '';
            }
        });
    }

    function loadProvinces(region) {
        if (!provinceSelect) return;

        provinceSelect.innerHTML = '<option value="">Loading...</option>';

        fetch('get-provinces.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'region=' + encodeURIComponent(region)
        })
            .then(response => response.json())
            .then(data => {
                provinceSelect.innerHTML = '<option value="">Select Province</option>';
                data.forEach(function (province) {
                    provinceSelect.innerHTML += '<option value="' + province + '">' + province + '</option>';
                });
                // Reset city
                if (citySelect) citySelect.innerHTML = '<option value="">Select Province First</option>';
                if (zipcodeInput) zipcodeInput.value = '';
            })
            .catch(error => {
                console.error('Error loading provinces:', error);
                provinceSelect.innerHTML = '<option value="">Error loading</option>';
            });
    }

    function loadCities(province) {
        if (!citySelect) return;

        citySelect.innerHTML = '<option value="">Loading...</option>';

        fetch('get-cities.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'province=' + encodeURIComponent(province)
        })
            .then(response => response.json())
            .then(data => {
                citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
                if (Array.isArray(data)) {
                    data.forEach(function (city) {
                        var cityName = city.name || city;
                        var zipcode = city.zipcode || '';
                        citySelect.innerHTML += '<option value="' + cityName + '" data-zipcode="' + zipcode + '">' + cityName + '</option>';
                    });
                }
            })
            .catch(error => {
                console.error('Error loading cities:', error);
                citySelect.innerHTML = '<option value="">Error loading</option>';
            });
    }

    function loadZipcode(city) {
        if (!zipcodeInput) return;

        fetch('get-zipcode.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'city=' + encodeURIComponent(city)
        })
            .then(response => response.text())
            .then(data => {
                zipcodeInput.value = data.trim();
            })
            .catch(error => {
                console.error('Error loading zipcode:', error);
            });
    }

    function loadBarangays(municipality) {
        if (!barangaySelect) return;

        barangaySelect.innerHTML = '<option value="">Loading...</option>';

        fetch('get-baranggays.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'municipality=' + encodeURIComponent(municipality)
        })
            .then(response => response.json())
            .then(data => {
                barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                data.forEach(function (barangay) {
                    barangaySelect.innerHTML += '<option value="' + barangay + '">' + barangay + '</option>';
                });
            })
            .catch(error => {
                console.error('Error loading barangays:', error);
                barangaySelect.innerHTML = '<option value="">Error loading</option>';
            });
    }
}

// Course/Major cascading dropdown
function initCourseMajorDropdown() {
    const courseSelect = document.getElementById('course');
    const majorSelect = document.getElementById('major');

    if (!courseSelect || !majorSelect) return;

    courseSelect.addEventListener('change', function () {
        const course = this.value;

        if (course) {
            loadMajors(course);
        } else {
            majorSelect.innerHTML = '<option value="">Select Major</option>';
        }
    });

    function loadMajors(course) {
        majorSelect.innerHTML = '<option value="">Loading...</option>';

        fetch('get-majors.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'course=' + encodeURIComponent(course)
        })
            .then(response => response.text())
            .then(data => {
                majorSelect.innerHTML = data;
            })
            .catch(error => {
                console.error('Error loading majors:', error);
                majorSelect.innerHTML = '<option value="">Error loading</option>';
            });
    }
}

// Calculate age from birthdate
function calculateAge() {
    const birthdateInput = document.getElementById('birthdate');
    const ageInput = document.getElementById('age');

    if (!birthdateInput || !ageInput) return;

    const birthdate = birthdateInput.value;
    if (birthdate) {
        const today = new Date();
        const birthDate = new Date(birthdate);
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        ageInput.value = age;
    } else {
        ageInput.value = '';
    }
}
