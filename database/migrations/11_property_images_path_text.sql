-- Supabase public URL'leri VARCHAR(255) sığmayabilir; tam URL saklamak için TEXT.
ALTER TABLE property_images
    ALTER COLUMN image_path TYPE TEXT;
