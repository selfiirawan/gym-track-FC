# Mini Project Journal

- **Student Name:** Selfi Ardi Irawan
- **Project Title:** GymTrack - Gym Membership Management System
- **Start Date:** 28/09/2026
- **End Date:** 12/10/2026

---

## Project Overview

> Briefly describe your project idea, what problem it solves, and what you aim to build.

GymTrack is a gym membership management system for managing members, staff, and admin tasks in one place. It aims to replace manual record-keeping with a role-based web app where each user type (member, staff, admin) sees only what is relevant to them.

---

## Tech Stack

> List the technologies, frameworks, and tools you plan to use.

- **Frontend:** HTML, CSS, JavaScript, Bootstrap
- **Backend:** PHP
- **Database:** MySQL
- **Other:**

---

## Day 1 — Date: 28/09/2026

### What I planned to do today
Completing the sidebar.

### What I actually did
Built the sidebar for all three roles: member, staff, and admin.

### Blockers / Challenges
The backend part was confusing. I wasn't sure how to write the logic so that each role gets its own sidebar.

### What I learned
- How to use `include __DIR__` to include shared files reliably.
- How to change the sidebar text and menu list based on which user is currently logged in.

---

## Day 2 — Date: 29/09/2026

### What I planned to do today
Finish the navbar and start on the main content.

### What I actually did
- Completed the navbar, except the search bar. It doesn't work yet; I'll build it when I start the `members.php` file.
- Completed the 4 stat cards.

### Blockers / Challenges
- Mostly CSS issues.
- The stat cards were harder than expected because of the PHP and SQL logic.

### What I learned
- The difference between `query()` and `prepare()`/`execute()`, and when to use each.
- How `fetchColumn()` works.

---

## Day 3 — Date: 30/09/2026

### What I planned to do today
Complete the entire dashboard page and the search bar logic.

### What I actually did
Completed the whole dashboard and the search bar logic. I can't fully test the search bar yet because I need to finish `members.php` first.

### Blockers / Challenges
Mostly on the backend: the PHP logic and the SQL queries.

### What I learned
- How to count "expiring soon" memberships with `DATEDIFF`.
- How to use `strtotime()` in PHP.
- How to use `BETWEEN` and `DATE_ADD` in SQL.

---

## Day 4 — Date: 01/10/2026

### What I planned to do today
- Complete the `members.php` page.
- Add pagination to the main table.
- Add the active state for the sidebar and the filter button.

### What I actually did
- Completed most of `members.php`. 
- Not done yet: the edit button in the action column, pagination, and the active state for the sidebar and filter. 
- I'm postponing the active stats for sidebar & filter btn, and the pagination features to focus on the edit button and the other pages first.

### Blockers / Challenges
Mostly on the backend and the SQL queries.

### What I learned
- The logic to add and delete a member.
- A shorter, more efficient way to build the logic for search, filter, and fetching all members together.
- `COUNT()` never returns `NULL`, but `SUM()`/`AVG()` do when there are no matching rows, hence `?? 0`.
- The `WHERE 1=1` trick for safely building dynamic filters with `AND`, no matter how many conditions apply.
- The `$params = []` pattern: collect only the values that actually need `?` placeholders.
- The `.=` string-append operator, used to build the SQL conditionally, similar to `+=`.
- `strtotime()` with `"+$duration days"`. 
- Learned about **Unix timestap** (a big number representing seconds since 1970).
- The difference between `??` , `?` and `:`.

---

## Day 5 — Date: 03/10/2026

### What I planned to do today
- Build the edit button in `members.php`.
- Start the `plans.php` page.

### What I actually did
- Completed the edit button in `members.php`.
- Started `plans.php`: the backend is done, only the front end is left.
- Decided to add a `features` column to `membership_plans` for per-plan benefit lists, using `ALTER TABLE`.
- Decided to make the membership plans page available to staff too, while keeping manage access for admin only.

### Blockers / Challenges
The logic for listing out each plan's benefits.

### What I learned
- `explode(',', $string)` splits a stored comma-separated string into an array.
- `trim()` is still needed alongside it to clean up whitespace.

---

## Day 6 — Date: 04/10/2026

### What I planned to do today
Complete the front end for `plans.php` and start the check-in page.

### What I actually did
- Completed the front end for `plans.php`, so `plans.php` is now fully done.
- Started the check-in page (`checkin.php`)and completed its backend logic.

### Blockers / Challenges
Mostly on the backend, especially the query for the check-in page.

### What I learned
- `number_format()`
- `date('g:i A', ...)` for formatting times
- How to create a search bar for the check-in page

---

## Day 7 — Date: 05/10/2026

### What I planned to do today
- Build the front end for `checkin.php`
- Create a dynamic search bar
- Start `payments.php`

### What I actually did
- Completed the full stack for `checkin.php`, including the dynamic search bar
- Started and completed `payments.php`
- Still to do: modify the search bar in the navbar

### Blockers / Challenges
Mostly on the backend and the SQL queries

### What I learned
- How to create a dynamic search bar and the logic behind it
- When to wrap output with `htmlspecialchars()` and when it isn't needed
- `ucfirst()`, which capitalizes only the first letter
- How to build the form and handle form submission specifically on the payment page
- The key decision for renewals: extend from today, or from the current expiry date?
- If the plan hasn't expired yet (early renewal), add the time on top of the existing expiry date, so the member doesn't lose their remaining days.
- If the plan has already expired, extend from today instead.

---

## Day 8 — Date: 06/10/2026

### What I planned to do today
- Modify the navbar search bar on the payments page
- Start `staff.php`

### What I actually did
- Modified the search bar in `payments.php`
- Started `staff.php`
- Created a new table for staff profiles
- Completed the backend for `staff.php`

### Blockers / Challenges
Mostly on the backend and the SQL queries

### What I learned
- How to modify the search bar depending on which page it is used on
- `beginTransaction()`, `commit()`, and `rollBack()`
- How to make sure staff can log in to the system once an admin adds them

---

## Day 9 — Date: ____

### What I planned to do today


### What I actually did


### Blockers / Challenges


### What I learned


---

## Day 10 — Date: ____

### What I planned to do today


### What I actually did


### Blockers / Challenges


### What I learned


---

## Day 11 — Date: ____

### What I planned to do today


### What I actually did


### Blockers / Challenges


### What I learned


---

## Day 12 — Date: ____

### What I planned to do today


### What I actually did


### Blockers / Challenges


### What I learned


---

## Day 13 — Date: ____

### What I planned to do today


### What I actually did


### Blockers / Challenges


### What I learned


---

## Day 14 — Date: ____

### What I planned to do today


### What I actually did


### Blockers / Challenges


### What I learned


---

## Final Reflection

### What went well?


### What would I do differently?


### Key takeaways from this project


