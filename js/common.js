/**
 * Common JavaScript functions for Classroom Management System
 */

$(document).ready(function() {
    // Handle file input display
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });
    
    // Enable tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Enable popovers
    $('[data-toggle="popover"]').popover();
    
    // Automatically dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert-success, .alert-info').fadeOut(500);
    }, 5000);
});

/**
 * Format dates in a user-friendly way
 * @param {string} dateString - The date string to format
 * @return {string} Formatted date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

/**
 * Confirm deletion with a modal dialog
 * @param {string} message - Confirmation message
 * @param {function} callback - Function to call on confirmation
 */
function confirmDelete(message, callback) {
    if (confirm(message || 'Are you sure you want to delete this item?')) {
        callback();
    }
}
