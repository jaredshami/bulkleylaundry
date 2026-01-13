# Latest News System Documentation

## Overview
The Bulkley Laundry latest news system uses individual JSON files for each news article, with a central index file for quick access.

## Directory Structure
```
/blogs/                    - Individual news JSON files (one per article)
  1.json                   - News article with ID 1
  2.json                   - News article with ID 2
  etc.
blogs.json                 - Index of all news articles (metadata only)
latest-news.html           - Latest news listing page
news-post.html             - Individual news article display page
admin-login.html           - Admin login page
admin-dashboard.html       - News management dashboard
save-blogs.php             - PHP backend for saving news articles
delete-blog.php            - PHP backend for deleting news articles
```

## How It Works

### Public-Facing Pages
1. **latest-news.html** - Displays all news articles sorted by date (newest first)
   - Reads from `blogs.json` index file
   - Shows title, date, author, category, and excerpt
   - Links to individual news articles

2. **news-post.html** - Displays individual news article
   - Accepts `?id=X` parameter in URL
   - Loads from `blogs/X.json` (full content)
   - Falls back to index if individual file not found

### Admin System
1. **admin-login.html** - Login page
   - Username: `admin`
   - Password: `Cr@ckerJ@cks`
   - Uses sessionStorage for authentication

2. **admin-dashboard.html** - Blog management
   - Add new blogs
   - Edit existing blogs
   - Delete blogs
   - Displays all blogs in reverse chronological order

### Backend Files
1. **save-blogs.php** - Saves blog data
   - Creates/updates individual blog file in `blogs/` directory
   - Updates the `blogs.json` index file
   - Validates all required fields
   - Returns JSON response with success/error status

2. **delete-blog.php** - Deletes blog data
   - Removes individual blog file from `blogs/` directory
   - Updates the `blogs.json` index file
   - Returns JSON response with success/error status

## Blog Data Structure

### blogs.json (Index File)
```json
[
  {
    "id": 1,
    "title": "Blog Title",
    "slug": "blog-title",
    "date": "2025-12-03",
    "author": "Bulkley Laundry",
    "category": "Laundry",
    "excerpt": "Brief summary of the blog post..."
  }
]
```

### blogs/1.json (Full Blog File)
```json
{
  "id": 1,
  "title": "Blog Title",
  "slug": "blog-title",
  "date": "2025-12-03",
  "author": "Bulkley Laundry",
  "category": "Laundry",
  "excerpt": "Brief summary of the blog post...",
  "content": "<h3>Heading</h3><p>Full HTML content here...</p>"
}
```

## Adding a Blog

1. Go to `admin-login.html`
2. Login with:
   - Username: `admin`
   - Password: `Cr@ckerJ@cks`
3. Fill out the form:
   - **Blog Title** - The main title of the post
   - **Author** - Defaults to "Bulkley Laundry"
   - **Date** - Publication date
   - **Category** - Choose from predefined categories
   - **Excerpt** - Brief summary (appears in listing)
   - **Blog Content** - Full content (supports HTML)
4. Click "Publish Blog"
5. Blog will appear in the list immediately

## Editing a Blog

1. Login to admin dashboard
2. Find the blog in the list
3. Click the "Edit" button
4. Update fields as needed
5. Click "Publish Blog" to save changes

## Deleting a Blog

1. Login to admin dashboard
2. Find the blog in the list
3. Click the "Delete" button
4. Confirm deletion
5. Blog will be removed immediately

## Blog Categories

- Laundry
- Fabric Care
- Tips & Tricks
- Industry News
- Cleaning

## Content Support

The blog content field supports HTML formatting:
- `<h2>`, `<h3>`, `<h4>` - Headings
- `<p>` - Paragraphs
- `<ul>`, `<ol>`, `<li>` - Lists
- `<strong>`, `<em>` - Text formatting
- `<br>` - Line breaks
- And other standard HTML tags

## File Permissions

The `blogs/` directory and individual JSON files need write permissions for the web server. On Windows with XAMPP, this is usually handled automatically, but on Linux/Unix systems, ensure proper permissions:

```bash
chmod 755 /xampp/htdocs/bulkleylaundry/blogs
chmod 644 /xampp/htdocs/bulkleylaundry/blogs/*.json
chmod 644 /xampp/htdocs/bulkleylaundry/blogs.json
```

## Troubleshooting

### Blog Not Saving
- Check that PHP is enabled on the server
- Verify `blogs/` directory exists and is writable
- Check browser console for JavaScript errors
- Verify all required fields are filled

### Blog Not Displaying
- Ensure blog JSON file exists in `blogs/` directory
- Check that `blogs.json` index is valid JSON
- Clear browser cache and reload
- Check browser console for fetch errors

### Blogs Directory Missing
- Create the `blogs/` directory manually if needed
- Set proper permissions (755 on Linux/Unix)
- Ensure PHP has write access

## Security Notes

⚠️ **Important**: This is a simple client-side authentication system suitable for development/internal use only. For production:

- Consider implementing proper backend authentication
- Use HTTPS for all connections
- Add CSRF protection
- Implement proper password hashing
- Consider adding audit logging
- Restrict access by IP or other methods

For more security, you could:
1. Use PHP sessions instead of sessionStorage
2. Implement database storage instead of JSON
3. Add rate limiting to blog operations
4. Implement proper user management
