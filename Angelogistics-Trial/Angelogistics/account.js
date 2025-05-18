document.addEventListener("DOMContentLoaded", () => {
    // --- Confirmation Modal Function ---
    const showConfirm = (message, yesCallback, noCallback) => {
        const confirmModal = document.getElementById('confirmModal');
        const confirmMessage = document.getElementById('confirmMessage');
        const confirmYes = document.getElementById('confirmYes');
        const confirmNo = document.getElementById('confirmNo');

        confirmMessage.textContent = message;

        confirmYes.onclick = () => {
            if (typeof yesCallback === 'function') {
                yesCallback();
            }
            confirmModal.style.display = 'none';
        };

        confirmNo.onclick = () => {
            if (typeof noCallback === 'function') {
                noCallback();
            }
            confirmModal.style.display = 'none';
        };

        confirmModal.style.display = 'flex'; // Changed to 'flex' to match modal style
    };

    const showNotification = (message) => {
        const modal = document.getElementById("notificationModal");
        const msg = document.getElementById("notificationMessage");
        msg.textContent = message;
        modal.style.display = "flex";
    };

    const closeNotificationBtn = document.getElementById("closeNotification");
    if (closeNotificationBtn) {
        closeNotificationBtn.addEventListener("click", () => {
            document.getElementById("notificationModal").style.display = "none";
        });
    }

    // --- Fetch Initial User Data ---
    fetch('account.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log("Data loaded successfully:", data);

                const profileImage = document.querySelector(".profile-image img");
                console.log("profileImage element (initial fetch):", profileImage);
                if (profileImage) {
                    profileImage.src = data.userData.profilePicture;
                    profileImage.dataset.original = data.userData.profilePicture; // Use data directly
                } else {
                    console.error("Could not find .profile-image img during initial fetch!");
                }

                document.getElementById("firstName").value = data.userData.firstName;
                document.getElementById("middleName").value = data.userData.middleName;
                document.getElementById("lastName").value = data.userData.lastName;
                document.getElementById("suffix").value = data.userData.suffix;
                document.getElementById("username").value = data.userData.username;
                document.getElementById("usernameDisplay").textContent = "@" + data.userData.username;
                document.getElementById("accountName").textContent = `${data.userData.firstName} ${data.userData.middleName} ${data.userData.lastName} ${data.userData.suffix || ''}`.trim();
                document.getElementById("role").value = data.userData.roleName;
                document.body.dataset.userRole = data.userData.roleName;

                // Initialize original values AFTER the data is loaded
                originalFirstName = firstNameInput.value;
                originalMiddleName = middleNameInput.value;
                originalLastName = lastNameInput.value;
                originalSuffix = suffixInput.value;
                originalUsername = usernameInput.value;
                originalImageSrc = profileImage.src;

                // Role-based UI adjustments (Create Account visibility)
                const createAccountContainer = document.querySelector(".create-account-container");
                const openCreateAccountModalBtn = document.getElementById("createAccountLink");
                if (data.userData.roleName === "Employee" && createAccountContainer) {
                    createAccountContainer.style.display = "none";
                } else if (openCreateAccountModalBtn && data.userData.roleName === "Admin") {
                    openCreateAccountModalBtn.style.display = "block"; // Explicitly show for admin
                } else if (openCreateAccountModalBtn) {
                    openCreateAccountModalBtn.style.display = "none"; // Hide for others
                }

                // --- **ADD THE MODAL DISPLAY LOGIC HERE** ---
                const createAccountModalOverlay = document.getElementById("createAccountModalOverlay");
                if (openCreateAccountModalBtn && document.body.dataset.userRole === "Admin") {
                    openCreateAccountModalBtn.addEventListener("click", () => {
                        console.log("Create New Account link clicked!");
                        createAccountModalOverlay.style.display = "flex";
                    });
                }

            } else {
                showNotification(data.message);
            }
        })
        .catch(error => {
            console.error("Fetch error:", error);
            showNotification("Failed to load account data.");
        });

    // --- Change Password Modal (Frontend and Backend Call) ---
    const openModalBtn = document.getElementById("openModal");
    const modalOverlay = document.getElementById("modalOverlay");
    const closeModalBtn = document.getElementById("closeModal");
    const changePasswordForm = document.getElementById("changePasswordForm"); // Correct form ID
    const newPass = document.getElementById("newPasswordChange"); // Correct input ID
    const confirmPass = document.getElementById("confirmPassword"); // Correct input ID
    const currentPass = document.getElementById("currentPassword"); // Correct input ID

    if (openModalBtn) {
        openModalBtn.addEventListener("click", () => {
            modalOverlay.style.display = "flex";
        });
    }

    if (closeModalBtn) {
        closeModalBtn.addEventListener("click", () => {
            modalOverlay.style.display = "none";
            if (changePasswordForm) changePasswordForm.reset();
        });
    }

    if (modalOverlay) {
        modalOverlay.addEventListener("click", (e) => {
            if (e.target === modalOverlay) {
                modalOverlay.style.display = "none";
                if (changePasswordForm) changePasswordForm.reset();
            }
        });
    }

    if (changePasswordForm) {
        changePasswordForm.addEventListener("submit", (e) => {
            e.preventDefault();
            if (newPass.value.length < 8) {
                showNotification("New password must be at least 8 characters long.");
                return;
            }
            if (newPass.value !== confirmPass.value) {
                showNotification("New password and confirmation do not match.");
                return;
            }

            const formData = new FormData();
            formData.append("action", "change_password");
            formData.append("currentPassword", currentPass.value);
            formData.append("newPassword", newPass.value);

            fetch("account.php", { // Updated URL
                method: "POST",
                body: formData,
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then((data) => {
                    if (data.success) {
                        showNotification(data.message);
                        modalOverlay.style.display = "none";
                        changePasswordForm.reset();
                    } else {
                        showNotification(data.message);
                    }
                })
                .catch((error) => {
                    console.error("Fetch error:", error);
                    showNotification("An error occurred while changing the password: " + error.message);
                });
        });
    }

    // --- Create New Account Modal (Frontend and Backend Call) ---
    const createAccountContainer = document.querySelector(".create-account-container");
    const openCreateAccountModalBtn = document.getElementById("createAccountLink");
    const createAccountModalOverlay = document.getElementById("createAccountModalOverlay");
    const closeCreateAccountModalBtn = document.getElementById("closeCreateAccountModal");
    const createAccountForm = document.getElementById("createAccountForm");
    const newMiddleNameInput = document.getElementById("newMiddleName");
    const newSuffixInput = document.getElementById("newSuffix");
    const newPasswordInput = document.getElementById("newPasswordCreate");

    if (openCreateAccountModalBtn && document.body.dataset.userRole === "Admin") {
        openCreateAccountModalBtn.addEventListener("click", () => {
            createAccountModalOverlay.style.display = "flex";
        });
    }

    if (closeCreateAccountModalBtn) {
        closeCreateAccountModalBtn.addEventListener("click", () => {
            createAccountModalOverlay.style.display = "none";
            if (createAccountForm) createAccountForm.reset();
        });
    }

    if (createAccountModalOverlay) {
        createAccountModalOverlay.addEventListener("click", (e) => {
            if (e.target === createAccountModalOverlay) {
                createAccountModalOverlay.style.display = "none";
                if (createAccountForm) createAccountForm.reset();
            }
        });
    }

    if (createAccountForm) {
        createAccountForm.addEventListener("submit", (e) => {
            e.preventDefault();
            const newFirstName = document.getElementById("newFirstName").value;
            const newLastName = document.getElementById("newLastName").value;
            const newUsername = document.getElementById("newUsername").value;
            const newPassword = newPasswordInput.value; // Corrected variable
            const newRole = document.getElementById("newRole").value;
            const newMiddleName = newMiddleNameInput ? newMiddleNameInput.value : "";
            const newSuffix = newSuffixInput ? newSuffixInput.value : "";

            if (!newFirstName || !newLastName || !newUsername || !newPassword || !newRole) {
                showNotification("All required fields are necessary to create a new account.");
                return;
            }

            if (newPassword.length < 8) {
                showNotification("Password must be at least 8 characters long.");
                return;
            }

            const formData = new FormData();
            formData.append("action", "create_account");
            formData.append("newFirstName", newFirstName);
            formData.append("newMiddleName", newMiddleName);
            formData.append("newLastName", newLastName);
            formData.append("newSuffix", newSuffix);
            formData.append("newUsername", newUsername);
            formData.append("newPassword", newPassword);
            formData.append("newRole", newRole);

            fetch("account.php", { // Updated URL
                method: "POST",
                body: formData,
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then((data) => {
                    if (data.success) {
                        showNotification(data.message);
                        createAccountModalOverlay.style.display = "none";
                        createAccountForm.reset();
                    } else {
                        showNotification(data.message);
                    }
                })
                .catch((error) => {
                    console.error("Fetch error:", error);
                    showNotification("An error occurred while creating the account: " + error.message);
                });
        });
    }

    // --- Profile Photo Upload ---
    const profileImage = document.querySelector(".profile-image img");
    const uploadPhotoButton = document.getElementById("uploadPhotoButton"); // Correct ID
    const cancelButtonSmall = document.getElementById("cancelUpload"); // Correct ID in HTML
    const imageInput = document.getElementById("imageInput");

    let selectedImageData = null;
    let originalImageSrc = profileImage ? profileImage.src : 'sidebar-icon/profile-pic-icon.png';

    if (uploadPhotoButton && imageInput) {
        const triggerImageInput = () => {
            imageInput.click();
            // Temporarily remove the event listener
            uploadPhotoButton.removeEventListener('click', triggerImageInput);
            // Re-add it after a short delay (to allow the file dialog to open)
            setTimeout(() => {
                uploadPhotoButton.addEventListener('click', triggerImageInput);
            }, 500); // Adjust the delay if needed
        };

        uploadPhotoButton.addEventListener("click", (event) => {
            event.preventDefault();
            triggerImageInput();
        });

        imageInput.addEventListener("change", () => {
            const file = imageInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    if (profileImage) profileImage.src = e.target.result;
                    selectedImageData = e.target.result.split(",")[1]; // Store base64
                    if (uploadPhotoButton) {
                        uploadPhotoButton.blur();
                    }
                };
                reader.readAsDataURL(file);
            } else {
                selectedImageData = null;
            }
            imageInput.value = ''; // Reset the input
        });
    }

    if (cancelButtonSmall) {
        cancelButtonSmall.addEventListener("click", (e) => {
            e.preventDefault();
            showConfirm("Are you sure you want to cancel the photo upload?", () => {
                // This is the 'yes' callback function
                if (profileImage) profileImage.src = profileImage.dataset.original || 'sidebar-icon/profile-pic-icon.png';
                selectedImageData = null;
                if (imageInput) {
                    imageInput.value = ''; // Clear the file input
                }
            });
            // No 'no' callback needed for this action, as we just stay on the page
        });
    }

    // --- Save / Cancel General Info ---
    const saveButton = document.getElementById("saveChanges"); // Correct ID
    const cancelButton = document.getElementById("cancelChanges"); // Correct ID

    const firstNameInput = document.getElementById("firstName");
    const middleNameInput = document.getElementById("middleName");
    const lastNameInput = document.getElementById("lastName");
    const suffixInput = document.getElementById("suffix");
    const usernameInput = document.getElementById("username");

    let originalFirstName = firstNameInput ? firstNameInput.value : '';
    let originalMiddleName = middleNameInput ? middleNameInput.value : '';
    let originalLastName = lastNameInput ? lastNameInput.value : '';
    let originalSuffix = suffixInput ? suffixInput.value : '';
    let originalUsername = usernameInput ? usernameInput.value : '';

    if (saveButton) {
        saveButton.addEventListener("click", () => {
            showConfirm("Save changes to your profile?", () => {
                const formData = new FormData();
                formData.append("action", "save_profile");
                formData.append("firstName", firstNameInput.value);
                formData.append("middleName", middleNameInput.value);
                formData.append("lastName", lastNameInput.value);
                formData.append("suffix", suffixInput.value);
                formData.append("username", usernameInput.value);

                if (selectedImageData) {
                    formData.append("profileImage", selectedImageData);
                }

                fetch("account.php", { // Updated URL
                    method: "POST",
                    body: formData,
                })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then((data) => {
                        if (data && data.success === true) {
                            originalFirstName = firstNameInput.value;
                            originalMiddleName = middleNameInput.value;
                            originalLastName = lastNameInput.value;
                            originalSuffix = suffixInput.value;
                            originalUsername = usernameInput.value;
                            if (profileImage) profileImage.dataset.original = profileImage.src;
                            showNotification(data.message);
                        } else {
                            showNotification(data ? data.message : "An error occurred while saving.");
                        }
                    })
                    .catch((error) => {
                        console.error("FETCH CATCH BLOCK EXECUTED:", error);
                        showNotification("An error occurred while saving: " + error.message);
                    });
            });
        });
    }

    if (cancelButton) {
        cancelButton.addEventListener("click", () => {
            showConfirm("Are you sure you want to cancel your changes?", () => {
                if (firstNameInput) firstNameInput.value = originalFirstName;
                if (middleNameInput) middleNameInput.value = originalMiddleName;
                if (lastNameInput) lastNameInput.value = originalLastName;
                if (suffixInput) suffixInput.value = originalSuffix;
                if (usernameInput) usernameInput.value = originalUsername;
                if (profileImage) profileImage.src = originalImageSrc;
                if (imageInput) imageInput.value = "";
                selectedImageData = null;
            });
        });
    }
});