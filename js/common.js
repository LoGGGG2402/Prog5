/**
 * Common JavaScript functions for classroom management system
 */

// Show file name when file is selected in a custom file input
$(document).ready(function() {
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Add confirmation dialog to delete buttons
    $('.btn-delete-confirm').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Handle message editing
    $('.edit-message').on('click', function() {
        let messageId = $(this).data('id');
        let messageText = $(this).data('message');
        $('#message_id').val(messageId);
        $('#message_text').val(messageText);
        $('#editMessageModal').modal('show');
    });
    
    // Handle message deletion
    $('.delete-message').on('click', function() {
        if (confirm('Are you sure you want to delete this message?')) {
            let messageId = $(this).data('id');
            $('#delete_message_id').val(messageId);
            $('#deleteMessageForm').submit();
        }
    });
});
