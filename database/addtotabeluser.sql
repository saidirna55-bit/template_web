ALTER TABLE `users`
ADD `google_id` VARCHAR(255) NULL AFTER `email`,
MODIFY `password` VARCHAR(255) NULL;