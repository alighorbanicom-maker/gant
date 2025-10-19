from playwright.sync_api import sync_playwright

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    # Navigate to the WordPress admin login page
    page.goto("http://localhost:8888/wp-login.php")
    page.fill('input[name="log"]', "admin")
    page.fill('input[name="pwd"]', "password")
    page.click('input[name="wp-submit"]')
    page.wait_for_load_state("networkidle")

    # Navigate to the AI Q&A page
    page.goto("http://localhost:8888/wp-admin/admin.php?page=wpm-ai-qa")
    page.wait_for_load_state("networkidle")

    # Fill out the form
    page.fill('input[name="project_duration"]', "365")
    page.fill('input[name="project_amount"]', "1000000000")
    page.fill('input[name="project_subject"]', "ساخت یک پل")
    page.fill('input[name="contract_type"]', "فهرست بهایی")
    page.fill('input[name="project_location"]', "تهران")
    page.fill('input[name="project_progress"]', "25")
    page.select_option('select[name="questioner_position"]', "پیمانکار")
    page.fill('textarea[name="question_description"]', "سوال در مورد شرایط عمومی پیمان")

    # Submit the form and wait for navigation
    page.click('input[type="submit"]')
    page.wait_for_load_state("networkidle")

    # Take a screenshot of the result
    page.screenshot(path="jules-scratch/verification/verification.png")

    browser.close()

with sync_playwright() as playwright:
    run(playwright)
