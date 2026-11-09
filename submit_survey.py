"""
Automated Google Form Submitter using Playwright for DRIVEED HUB Survey.
Guarantees:
- Page 2 Consent: 100% strictly clicks 'Yes, I agree to participate'
- Page 3 Role: Configurable via --role ('student', 'instructor', 'admin', 'manager')
- Evaluation pages: Only selects 'Agree' or 'Strongly Agree' with configurable probability
- Feedback: Left blank

Supports:
- Roles: Student, Instructor, School Administrator, Branch Secretary / Manager
- Weighted choices (--agree-chance)
- Paced / Interval mode (--paced)
- Sequential mode (default)
"""

import argparse
import datetime
import random
import sys
import time
from playwright.sync_api import sync_playwright

FORM_URL = "https://docs.google.com/forms/d/e/1FAIpQLSd9u6KQ2wOcHt-aNPQVoSfc43VX2ypvmxD1ZufXpLqAlfUPMg/viewform"
CHROME_PATH = r"C:\Program Files\Google\Chrome\Application\chrome.exe"

ROLES_MAP = {
    "student": "Student / Student Driver",
    "instructor": "Instructor",
    "admin": "School Administrator",
    "school administrator": "School Administrator",
    "manager": "Branch Secretary / Manager",
    "secretary": "Branch Secretary / Manager",
}

def resolve_role(role_arg):
    key = (role_arg or "student").strip().lower()
    return ROLES_MAP.get(key, role_arg)

def submit_one(idx_label="1/1", agree_chance=0.75, role="Student / Student Driver"):
    now_str = datetime.datetime.now().strftime("%H:%M:%S")
    print(f"[{now_str}] [{idx_label}] Starting submission (Role: {role} | Agree weight: {int(agree_chance * 100)}%)...")
    start_t = time.time()
    with sync_playwright() as p:
        browser = p.chromium.launch(executable_path=CHROME_PATH, headless=True)
        page = browser.new_page()

        # Navigate with retry
        for attempt in range(3):
            try:
                page.goto(FORM_URL, timeout=30000)
                page.wait_for_load_state("networkidle")
                break
            except Exception as e:
                if attempt == 2:
                    browser.close()
                    raise e
                time.sleep(2)

        for step in range(1, 16):
            time.sleep(0.5)

            # 1. Handle Consent (Strictly target 'Yes, I agree to participate')
            consent = page.query_selector('div[role="radio"][data-value="Yes, I agree to participate"]')
            if consent:
                consent.click()
                time.sleep(0.2)

            # 2. Handle Role dropdown
            dropdown = page.query_selector('div[role="listbox"]')
            if dropdown:
                dropdown.click()
                time.sleep(0.3)
                opt = page.query_selector(f'div[role="option"][data-value="{role}"], div[role="option"]:has-text("{role}")')
                if opt:
                    opt.click()
                time.sleep(0.3)

            # 3. Handle Likert Questions with weighted probability
            radio_groups = page.query_selector_all('div[role="radiogroup"]')
            for rg in radio_groups:
                rg_radios = rg.query_selector_all('div[role="radio"]')
                agree_r = None
                strongly_agree_r = None
                for r in rg_radios:
                    val = (r.get_attribute('data-value') or '').strip().lower()
                    if val == 'agree':
                        agree_r = r
                    elif val == 'strongly agree':
                        strongly_agree_r = r

                if agree_r and strongly_agree_r:
                    choice = agree_r if random.random() < agree_chance else strongly_agree_r
                    choice.click()
                    time.sleep(0.05)
                elif agree_r:
                    agree_r.click()
                    time.sleep(0.05)
                elif strongly_agree_r:
                    strongly_agree_r.click()
                    time.sleep(0.05)

            # 4. Check for Submit button
            submit_btn = page.query_selector('//span[text()="Isumite" or text()="Submit"]')
            if submit_btn:
                submit_btn.click()
                time.sleep(2.5)
                confirm = page.inner_text('body')
                elapsed = round(time.time() - start_t, 1)
                if 'recorded' in confirm or 'tugon' in confirm:
                    print(f"[{datetime.datetime.now().strftime('%H:%M:%S')}] [{idx_label}] SUCCESS: Recorded in {elapsed}s.")
                    browser.close()
                    return True
                browser.close()
                return False

            # 5. Check for Next button
            next_btn = page.query_selector('//span[text()="Susunod" or text()="Next"]')
            if next_btn:
                next_btn.click()
                time.sleep(0.5)
            else:
                break

        browser.close()
        return False

def run_paced(total_target, min_rate=1, max_rate=3, interval_seconds=60, agree_chance=0.75, role="Student / Student Driver"):
    print(f"=== Starting Paced Interval Mode ===")
    print(f"Role: {role}")
    print(f"Total target: {total_target if total_target else 'Infinite (until stopped)'}")
    print(f"Rate: {min_rate} to {max_rate} submissions per {interval_seconds}s interval")
    print(f"Agree Chance: {int(agree_chance * 100)}% Agree / {int((1 - agree_chance) * 100)}% Strongly Agree\n")

    completed = 0
    minute = 1

    try:
        while True:
            if total_target and completed >= total_target:
                break

            remaining = (total_target - completed) if total_target else max_rate
            count_this_block = random.randint(min_rate, max_rate)
            count_this_block = min(count_this_block, remaining)

            block_start = time.time()
            now_str = datetime.datetime.now().strftime("%H:%M:%S")
            print(f"--- [Minute {minute}] [{now_str}] Scheduled: {count_this_block} response(s) ---")

            for i in range(1, count_this_block + 1):
                idx_label = f"{completed + 1}/{total_target}" if total_target else f"#{completed + 1}"
                ok = submit_one(idx_label, agree_chance=agree_chance, role=role)
                if ok:
                    completed += 1

                if i < count_this_block:
                    pause = random.uniform(5.0, 15.0)
                    print(f"  -> Waiting {pause:.1f}s before next submission in this block...")
                    time.sleep(pause)

            elapsed_block = time.time() - block_start
            time_left = interval_seconds - elapsed_block
            if time_left > 0 and (not total_target or completed < total_target):
                print(f"--- [Minute {minute} Done] Resting {time_left:.1f}s until next minute... ---\n")
                time.sleep(time_left)
            else:
                print(f"--- [Minute {minute} Done] Completed in {elapsed_block:.1f}s. ---\n")

            minute += 1

    except KeyboardInterrupt:
        print("\nProcess interrupted by user.")

    print(f"\nAll done! Successfully submitted {completed} response(s) across {minute - 1} minute(s).")

def run_sequential(count, delay=1.0, agree_chance=0.75, role="Student / Student Driver"):
    print(f"Starting sequential submission of {count} response(s)...")
    print(f"Role: {role} | Probability: {int(agree_chance * 100)}% Agree / {int((1 - agree_chance) * 100)}% Strongly Agree\n")
    successes = 0
    for i in range(1, count + 1):
        try:
            if submit_one(f"{i}/{count}", agree_chance=agree_chance, role=role):
                successes += 1
        except Exception as e:
            print(f"[{i}/{count}] Error: {e}")
        if i < count:
            time.sleep(delay)
    print(f"\nAll done! {successes}/{count} responses submitted.")

def main():
    parser = argparse.ArgumentParser(description="Submit automated survey responses for DRIVEED HUB.")
    parser.add_argument("-n", "--count", type=int, default=1, help="Total number of responses to submit (default: 1)")
    parser.add_argument("-r", "--role", type=str, default="student", choices=["student", "instructor", "admin", "manager"], help="Role to select (student, instructor, admin, manager - default: student)")
    parser.add_argument("-p", "--paced", action="store_true", help="Enable paced interval mode (random per minute)")
    parser.add_argument("--min-rate", type=int, default=1, help="Min submissions per interval (default: 1)")
    parser.add_argument("--max-rate", type=int, default=3, help="Max submissions per interval (default: 3)")
    parser.add_argument("--interval", type=int, default=60, help="Interval window in seconds (default: 60)")
    parser.add_argument("-d", "--delay", type=float, default=1.0, help="Delay for sequential mode (default: 1.0s)")
    parser.add_argument("--agree-chance", type=float, default=0.75, help="Probability of choosing Agree over Strongly Agree (default: 0.75)")
    args = parser.parse_args()

    role_text = resolve_role(args.role)

    if args.paced:
        run_paced(
            total_target=args.count,
            min_rate=args.min_rate,
            max_rate=args.max_rate,
            interval_seconds=args.interval,
            agree_chance=args.agree_chance,
            role=role_text
        )
    else:
        run_sequential(args.count, args.delay, agree_chance=args.agree_chance, role=role_text)

if __name__ == "__main__":
    main()
