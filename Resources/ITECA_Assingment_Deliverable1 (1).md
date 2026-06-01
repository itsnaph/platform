

---
<!-- Page 1 -->

ITECA3-B12 – Project – Deliverable 1
Page 1 of 15

# Project Deliverable 1 – Project Proposal


## Faculty Name:

Information Technology

## Module Code:

ITECA3-12

## Module Name:

Web Development and e-Commerce

## Content Writer:

Munashe Tsikada

## Copy Editor:

Mr Kyle Keens

## Submission Date:


## Student Name

Munashe Tsikada

## Student Number

EDUV4881584

## Project Title

ITECA3-12

## Submission Date

27/02/2026


---
<!-- Page 2 -->

ITECA3-B12 – Project – Deliverable 1
Page 2 of 15

# Table of contents


## Contents

1.1  Introduction ..................................................................................................... 3
1.2  Needs / Problems ......................................................................................... 4
1.3  Goals / Objectives ........................................................................................ 7
1.4)  Procedures / Scope of Work ......................................................................... 9
1.6  Conclusion ..................................................................................................13
Resources .............................................................................................................15


---
<!-- Page 3 -->

ITECA3-B12 – Project – Deliverable 1
Page 3 of 15

## 1.1  Introduction

South Africa's e-commerce sector is expanding rapidly. According to World Wide Worx
and Mastercard (2025), the country's online retail turnover could exceed R130 billion in
2025, with digital platforms now making up almost 10% of all retail spend. What is
notable about this growth is that a large portion of it isn't occurring in the formal retail.
More and more people are venturing into the consumer to consumer platforms to make
money, and many of them are doing so without any kind of business registration or
dedicated digital infrastructure (Standard Bank, 2025). The opportunity created by this
is significant, but so are the gaps it reveals.
Those gaps are most evident at the level of everyday informal work. In most South
African suburbs and townships, community WhatsApp groups are filled with the same
types of requests every week: someone wants a gardener, a painter for the outside
walls, a cleaner for a once off job or a pair of hands for a move. These requests are filled
by word of mouth and the entire arrangement is played out on the trust, cash in hand,
and hope that things will go well on the day. There is no means to check a worker's track
record before employing them, no payment protection if the job is not done right, and no
means for the worker to build a reputation that follows from client to client.
The scale of the problem is obvious if you look at the employment figures. As of the first
quarter of 2025 South Africa's official unemployment rate stands at 32.9% and the
expanded rate, which includes the number of people who have given up looking for
work, at 43.1% (Statistics South Africa, 2025). Among young people between the ages of
15 and 24, that number rose to 62.4%. The Brookings Institution highlights that South
Africa is located in the context of one of the highest youth unemployment contexts on
the African continent - and digital platforms are one of the only realistic pathways to
income inclusion on this scale (Nguimkeu, 2025). The informal economy, which takes in
a large proportion of these workers, currently lacks the infrastructure to make that
pathway work fairly or reliably.
SweepSouth, which was launched in 2014, is frequently cited as proof that a market for
digitised domestic services exists in South Africa. With more than 1.2 million cleaners
registered on it, the demand is clearly there (Rest of World, 2024). But SweepSouth has
been widely criticised for the way it treats the workers on which it depends. According to
an investigation by Rest of World (2024), workers can be blocked from the app
permanently after a single complaint from a client, with no possibility of responding or
defending themselves. Clients occasionally require extra work, which is not paid, and
workers agree because of fear of being flagged. There is no neutral mechanism for
resolving disputes and payment is not held in such a way as to protect workers from
clients who refuse to confirm that a job has been completed.


---
<!-- Page 4 -->

ITECA3-B12 – Project – Deliverable 1
Page 4 of 15
HustleHub is a C2C service marketplace that is designed to solve these specific
failures. Workers list their services at a fixed price and clients book directly as is done in
the case of Fiverr. The defining feature is an escrow payment system: the client makes a
payment when he or she books, the money is held until the job has been confirmed as
completed by the client, and then it is released to the worker. If there is a disagreement,
an admin is able to take over and see both sides of the argument and determine how the
funds should be handled. The platform is focused on physical household activities:
cleaning, gardening, painting, furniture moving and minor repairs. Keeping the scope of
this narrow allows the platform to be simple to use and realistic to build within the
project time frame using the stack of required technologies in this example (html, css,
JavaScript, PHP, and MySQL).
Sources for Section 1.1: World Wide Worx and Mastercard 2025 Standard Bank 2025
Statistics South Africa 2025 Nguimkeu 2025 Rest of World 2024

## 1.2  Needs / Problems

1.2.1 Who This Platform Is For
The main users of HustleHub are informal physical service workers who are based in
townships and peri-urban communities. These are people who provide cleaning,
gardening, painting, moving, and general repair services but currently have no
structured way of advertising and accepting bookings. According to the SweepSouth's
annual report for 2024, there are more than 1.2 million domestic workers in South Africa
and 92% of them are women (SweepSouth, 2024). Statistics South Africa revealed that
843 000 domestic workers were formally employed for Q2 2024 - which translates to a
large number working in a position that still does not have a contract, is not registered
with the UIF, or does not have any sort of protection (BusinessTech, 2024).
The secondary users are clients, which is the homeowners, tenants, and small property
managers who already look for these services through Facebook groups and
community chats but have no quality assurance, verified profiles, or payment
protection available to them. Both groups are at actual danger in all informal
transactions that they enter.
1.2.2) How Long This Problem Has Been in Existence
Informal domestic and physical service work in South Africa is a pre-internet
phenomenon. It has always been arranged through personal referrals and face-to-face
trust. The one significant attempt to digitise it at scale has been SweepSouth, which has
been operating since 2014. Over ten years later, the fundamental problems of worker
vulnerability and lack of payment protection are still not resolved, and there has been
no alternative local platform that has challenged the status quo.


---
<!-- Page 5 -->

ITECA3-B12 – Project – Deliverable 1
Page 5 of 15
SweepSouth's own 2024 report documents that in just a year of time the cost of living
for workers rose by 15% while their earnings only rose by 5% (SweepSouth, 2024). The
minimum wage for domestic workers was fixed at R28.79 per hour in 2025, however, the
average worker on SweepSouth is paid about R3 349 per month, which is less than what
a full-time worker on the above hours should be earning at that rate (BusinessTech,
2025). Seventy-seven percent of domestic workers surveyed were not registered with
UIF by their employer (BusinessTech, 2024). These figures attest that the existing digital
infrastructure has not improved material conditions, nor has anything replaced it with
something fairer.
1.2.3) Platforms that Exist and Are Lacking
SweepSouth Launched 2014. More than 1.2 million cleaners registered. Workers can
be permanently blocked after one complaint from the client without any process for
self-defence (Rest of World, 2024).
Workers are routinely asked to do unpaid additional duty. No escrow or payment
protection. There is no neutral mechanism for resolving a dispute.
SweepSouth has recognised these concerns but has not acted on them at a structural
level (Rest of World, 2024).
Facebook Marketplace Free and widely used but does not provide a booking system, no
payment protection, no rating or verification system.
Commonly used for informal service advertising in townships but entirely exposes both
parties.
WhatsApp Community Groups- The most common way that township residents find
local service workers. Completely informal, no verification, no receipts, no recourse.
Workers have no opportunity to bring a verifiable reputation from one client to the next.
The pattern is the same with all three: current choices either open workers to
exploitation or provide no protection for anyone. HustleHub is designed to fill the gap
that none of them currently occupy: a local, mobile-friendly platform where both sides
of a service transaction are treated fairly.
1.2.4) The Five Core Problems
No payment protection to workers. Informal workers are paid in cash after the job, if at
all. On SweepSouth, payment flows through the platform, however, there is no
mechanism to prevent a client from refusing to mark a job as complete. Workers doing
unpaid over hours is a documented pattern in the Rest of World 2024 investigation.


---
<!-- Page 6 -->

ITECA3-B12 – Project – Deliverable 1
Page 6 of 15
HustleHub solves this with a holding of client payment in escrow from the time of
booking, and the worker receives it once the job is confirmed done.
No quality assurance to clients. Clients that get workers via WhatsApp or Facebook, for
instance, have no way to vet ratings, prior work or verify identity before committing.
Makhitha and Ngobeni (2023) found that two factors stand out as most important in
determining whether township consumers adopt online platforms at all; perceived ease
of use and trust. Without a transparent profile and rating system, the risk is all put on
the client every time.
No neutral system of dispute resolution. When something goes wrong on SweepSouth,
the outcome is left to its discretion. Workers report that the default platform is the
client's account, because clients are the fee paying customers of the platform itself
(Rest of World, 2024). On HustleHub, disputes are handled by an admin with access to
both sides of the booking record who can release or refund the escrow funds based on
what the evidence actually shows.
Workers lack opportunity to generate a digital reputation. A cleaner who has done
excellent work in thirty households has little to offer a new client except his word.
Nguimkeu (2025) has identified the creation of digital identities for informal workers as
one of the most valuable contributions that a gig platform can make in a developing-
economy context. HustleHub's profile and review system does just that: every
completed booking contributes a verifiable piece of data for a worker's public profile.
Most platforms are not designed for township users. Existing platforms are targeted for
users with reliable data connections and mid-range devices. Township communities
also still bare the greater cost of data, and often make use of entry level Android
handsets. A slow-to-load or confusingly navigable platform on a small screen will
simply not be used no matter how good the underlying idea is. HustleHub is mobile-first
from the beginning, and the feature set is kept intentionally minimal so that the user can
navigate through it with any basic smartphone.
1.2.5) Effect on Target and Surrounding Populations
The combined result of these five issues is that township service workers are locked out
of consistent, protected income. They have no means of proving their quality to
strangers, of having recourse when clients exploit them, or of having the digital record of
the work they have already done well. The surrounding economic impact is equally real:
Money spent through informal, untracked transactions does not support the sort of
commercial activity that creates jobs and puts money back into communities. Standard
Bank (2025) puts the economy of the township at almost R900 billion a year and much
of that involves service transactions taking place without any digital record or
protection. One verified booking at a time, HustleHub's aim is to change that.


---
<!-- Page 7 -->

ITECA3-B12 – Project – Deliverable 1
Page 7 of 15
Sources: Statistics South Africa (2025), SweepSouth (2024), Rest of World (2024),
BusinessTech (2024, 2025), Nguimkeu (2025), Makhitha and Ngobeni (2023), Standard
Bank (2025).

## 1.3  Goals / Objectives


## Goal 1: Develop a C2C service booking platform with escrow payment that works

Build a web application (full stack) in the web (using your own web server) where
workers can post the physical services they offer at a fixed price and clients can book
and pay for it. The escrow model collects payment at the point of booking it and holds it
in the transactions table with a status of 'held'. The status is only changed to 'released'
when the client confirms that the job is done. This is a direct answer to the payment
protection issue.
• Success measure: Each booking that is completed passes through the escrow
model. There is no booking that can be marked complete unless there is some
client confirmation or an administrative override. The transactions table reflects
the correct status of the booking lifecycle at each stage of the booking lifecycle.
• Benefit: Workers are paid guaranteed money once they have a job confirmed to
be completed. Clients may not receive a service and walk away without initiating
a dispute process.

## Goal 2: Develop a worker profile system and two-way star ratings

Workers will have to review their account before any listing goes live. After a booking is
completed, both client and worker rate each other on a 1 to 5 star scale and have a
written comment. Worker profiles show the average rating, number of jobs completed,
and all individual reviews. This clears both the client quality issue and lack of digital
reputation for workers.
• Success measure: No listing is visible in the list until worker's account is verified.
Every completed booking results in a rating prompt for both parties. Worker
profiles display correct aggregate ratings based on actual entries in the reviews
table.
• Benefit: Clients have the ability to make informed decisions before booking.
Workers build up a track record that can be verifiable and increases with each
job performed and decreases the cold start problem with each new client.

## Goal 3: Develop an admin dispute module with manual escrow control

The admin portal should have a provision for either party to raise a dispute of any active
or completed booking. When a dispute is raised, the admin will be notified and will be
able to view the statements of both parties and the full booking history and choose to
release the funds to the worker, issue full or partial refund to the client, or ask for more


---
<!-- Page 8 -->

ITECA3-B12 – Project – Deliverable 1
Page 8 of 15
information before making a decision. Every action of an admin is recorded with the
timestamp and the ID of the admin's account.
• Success measure: The complete workflow of the dispute is tested end to end:
dispute raised, admin notified, admin reviews and acts, transaction status
updated, resolution note saved. At least two admin roles are implemented:
Super Admin and Moderator which have defined permissions.
• Benefit: Neither party has unilateral power of the outcome of a dispute. The
admin acts as a neutral middleman with a full audit trail for reference, which is
precisely what is lacking from all existing platforms in this space.

## Goal 4: Design and create a mobile first design that works on a 3G connection

All the pages are created mobile first using Bootstrap 5's grid system, with the media
queries for breakpoints at 320px, 768px and 1024px. Images use lazy loading. All main
pages should load and work correctly on a simulated 3G connection using the Chrome
DevTools and the platform should be tested physically on at least one Android device
before being submitted.
• Success measure: All of the core pages load in 4 seconds using a simulated 3G
connection. The platform works perfectly on screens that are 320px wide and
above. Physical testing of an Android device is covered in project
documentation.
• Benefit: The platform is truly available to the township users for whom it is
designed, not just the fast connected and newer handsets.

## Goal 5: Successfully go through end-to-end booking cycles in user acceptance


## testing

Before submitting, the platform must prove at least three full cycles of bookings in user
acceptance testing. Every cycle must span the entire workflow from a worker listing a
service, to a client booking and simulating payment, to the booking moving to
completion, to the client confirming the job completed so that the escrow funds are
released. At least one of these cycles needs to include a dispute being brought up and
resolved by an admin. This validates the core logic of the platform to function as a
connected system, and not as individual features disconnected from one another.
• Success measure: Three complete cycles of bookings are recorded with
screenshots at each phase. One cycle of dispute is completed with an admin
resolution added to the disputes table. All the transitions of the escrow status
are validated as correct in the transactions table at all the test cycles.
• Benefit: This confirms that the platform works as a real system and not a series
of separate pages. It also gives concrete evidence to the Deliverable 3
presentation and a User Manual has real data to document.


---
<!-- Page 9 -->

ITECA3-B12 – Project – Deliverable 1
Page 9 of 15
Sources for Section 1.3: Rest of World (2024); Statistics South Africa (2025); Nguimkeu
(2025); Makhitha and Ngobeni (2023).

## 1.4)  Procedures / Scope of Work

1.4.1) What Is Being Built
HustleHub is a collection of two web applications, which are using one MySQL
database. The first is the main marketplace, which is used by workers and clients. The
second is an admin portal that is only viewable by people with an admin or moderator
role, this is secured using PHP session checks on every page. Both applications are
designed using the module's required stack, which is the following: HTML5, CSS3 with
Bootstrap 5, JavaScript with jQuery for the development of the AWS (Ajax Web
Services), PHP 8.x for the implementation of all the logic that the server performs or
PHP 8.x to the data storage, which is MySQL 8.x. There are no CMS tools like WordPress
or Wix used. Development is done locally using Vscode, Version control is done using
GitHub, and the platform is hosted on InfinityFree for the final presentation.
The system is deliberately kept simple. The entire platform is based on six tables in a
database and the trickiest logic is a booking status field which goes through a defined
process of states. This is a normal CRUD application with booking and escrow flow. It is
buildable within the timeframe by one developer.

## Table


## What It stores


## Users

All users: workers, clients, and admins. A role column controls
access. OTP verification status is tracked here.

## Services

Service listings created by workers: title, description, category,
fixed price, estimated duration, and admin approval status.

## Bookings

Each booking a client makes. Status progresses through:
pending, confirmed, in_progress, completed, disputed, or
cancelled.

## Transactions

One financial record per booking: the amount, and escrow
status (held, released, or refunded). Admin can update this
during a dispute.

## Reviews

Post-completion ratings from both parties: star score (1 to 5),
written comment, linked to the booking to prevent duplicates.

## Disputes

Dispute records: which booking is disputed, who raised it, the
reason, current status (open or resolved), admin resolution
note, and timestamps.
The basic flow of the booking process is as follows: the worker lists a service, the client
books and pays, the worker does the job, the client verifies it's done and the escrow
funds are released. If the client is unsatisfied, they initiate a dispute and the admin
intervenes. That is the whole core logic of the platform.


---
<!-- Page 10 -->

ITECA3-B12 – Project – Deliverable 1
Page 10 of 15
1.4.2)  Phase 1 Research and Requirements (Weeks 1 to 3)
This phase includes the literature and background research that is used to inform the
proposal. It also generates the system design documents needed in Deliverable 2: CRC
cards for each entity, an EERD mapping the six tables and their relationships, a Context
Diagram to define the system boundary and a Level 1 DFD to trace the booking,
payment and dispute processes. Use case scenarios are written for three types of
actors - Worker, Client and Administrator.
1.4.3) Phase 2: Prototyping (Weeks 3 to 4)
Responsive wireframes for all the main pages are made on Figma before the code is
ever written. The pages that were required to have wireframes are: Landing and Browse,
Worker Profile, Service Detail and Booking, Client Dashboard, Worker Dashboard,
Admin Dashboard, Dispute Panel, and Login and Registration. Each of the wireframes
shows three breakpoints for mobile (320px to 767px), tablet (768px to 1024px), and
desktop (1025px). The HustleHub colour scheme incorporates navy, orange and white
across the board.
1.4.4 ) Phase 3: Database and Backend (Weeks 4 until 6)
Database setup comes first: all of the six tables are created in phpMyAdmin with the
correct column type, foreign key constraints, and test seed data. PHP development is
based on a simple folder structure, where the files of the database connection are
placed in the folder config/, the files of pages that can be used in other pages are placed
in the folder includes/, the files of pages go to the folder pages/, and finally, the files of
the admin portal go to the folder admin/. The core PHP modules are five and are as
follows:
• User Authentication: Registration (with email based (OTP) verification), login
(using PHP Sessions), Password hashing using (BCRYPT) and role based
redirects (to ensure that workers, clients and admins land on the right dash
board after login).
• Service Listings: Workers have the ability to create, edit and delete their own
listings. Admins can approve or reject listings from the admin panel before the
listings go public. Clients have the ability to browse by category and keyword
search.
• Booking and Escrow: The client chooses a service, selects a date for the service,
and confirms the booking. A simulated payment logs a row of transactions with
status 'held'. The booking status is changed as the job progresses. When the
client clicks to confirm the completion status, the PHP logic is changed to
'released' for the transaction status.
• Disputes: Either party may initiate a dispute on an active / completed booking.
The admin views all the open disputes in the portal and can view the booking


---
<!-- Page 11 -->

ITECA3-B12 – Project – Deliverable 1
Page 11 of 15
history and statements of both the parties and update the transaction to
'released' or 'refunded' with a written resolution note.
• Reviews: Once a booking is set as complete, each party would be asked to rate
the other. The star rating and comment are written to the reviews table and the
average rating for the worker is recalculated and displayed on their profile.
Note on payments: payment is simulated in this project, instead of integrated with a live
gateway. The reason for this is to illustrate the logic of escrow, not to process real
transactions. A "Pay Now" button logs the payment as "held" in the database. This is an
orthodox method for academic web development projects at this level.
1.4.5) Phase 4: Frontend (Weeks 5 to 7)
The addition of all the functionality is done in a mobile-first way with the help of
Bootstrap 5's grid system that is written in all the different types of CSS and HTML.
JavaScript is used in the site for jQuery, Ajax based booking status updates, dynamic
filtering of listings based on category and price, character counter on review input
fields, and for image upload previews on the service listing form. No frontend
framework apart from Bootstrap and jQuery is used. This keeps the codebase within the
requirements of the module and manageable by a single developer.
1.4.6) Phase 5: Testing (Week 7)
Testing is conducted in three fields. Functional testing is executed in each booking flow,
escrow transition, dispute path, and admin action against a written list of test cases.
Responsive testing uses Chrome Devtools to use device emulation to test layouts in
Chrome on a Galaxy A-series phone, an iPad mini, and a 1920px desktop with a physical
Android device used to test for real world confirmation. Security testing checks that
user inputs to the site are processed using PDO prepared statements, that the output of
any web page has properly escaped, that all web forms have CSRF tokens, and that the
session IDs are regenerated when a user logs in.
1.4.7) Phase 6 User Onboarding and Training (Weeks 7 to 8)
Since HustleHub is aimed at users that may not have experience with structured digital
booking platforms, the onboarding experience is part of the build rather than an
afterthought. Workers and clients will find their way through a brief walkthrough given
on their first login, with a step-by-step welcome flow that's built in and written in
JavaScript and points out some of the key actions: creating a listing for workers, and
searching and booking a service for clients. This walkthrough can be dismissed and
accessed again at anytime from the user dashboard.
In addition, there will be a dedicated Help page within the platform, with the most
common questions to every type of user in simple terms. These include how the escrow


---
<!-- Page 12 -->

ITECA3-B12 – Project – Deliverable 1
Page 12 of 15
system works, what happens in the event of a dispute being raised, and how to update
or remove a listing. The User Manual produced as a part of Deliverable 3 will be used as
the extended version of this guidance, with annotated screenshots providing step-by-
step walk-through of every main feature. Together, the in-platform walkthrough, the
Help page, and the User Manual make up the bulk of the training and support plan for
HustleHub.
1.4.8) Phase 7: Implementation: And Writing It Up (Weeks 7 to 8)
The platform is deployed to InfinityFree at least a week before the presentation of the
Deliverable 3, as it is required by the project specification. The GitHub repository is
made accessible for submitting code. The User Manual is created using minimum 15
annotated screenshots. All of the Deliverable 2 documentation is finished and posted
by Block 2 Week 5.
Development Tools: VS Code, XAMPP, Figma, GitHub, InfinityFree, Chrome DevTools.
Stack: HTML5, CSS3, Bootstrap 5, JavaScript ES6, jQuery 3.x, PHP 8.x, MySQL 8.x.

## 1.5) Timetable


## Deliverable


## Key Activities


## Start to End


## Deliverable 1

Literature research, problem analysis,
comparator review (SweepSouth,
Facebook, WhatsApp), goal formulation,
scope of work, Gantt chart, proposal
writing.
Block 1, Week 1 to
Block 1, Week 4

## Deliverable 2

Figma wireframes for all pages across
three breakpoints, system diagrams
(EERD, DFD, Context Diagram, Use Case
Diagram, CRC cards), MySQL schema,
PHP backend, HTML/CSS/JS frontend,
escrow and booking logic, testing,
documentation.
Block 1, Week 3 to
Block 2, Week 5

## Deliverable 3

Live deployment to public hosting, user
acceptance testing (three complete
booking cycles including one dispute),
User Manual production with 15 or more
annotated screenshots, presentation
preparation.
Block 2, Week 6 to
Summative Schedule


---
<!-- Page 13 -->

ITECA3-B12 – Project – Deliverable 1
Page 13 of 15
1.5.1) Gantt Chart

### Activity


### Wk 1


### Wk 2


### Wk 3


### Wk 4


### Wk 5


### Wk 6


### Wk 7


### Wk 8

D1: Background Research
●
●
D1: Problem Analysis
●
●
●
D1: Proposal Writing
●
●
●
D2: Wireframes (Figma)
●
●
D2: System Diagrams
●
●
●
D2: MySQL Database Schema
●
●
D2: PHP Backend
●
●
●
D2: HTML / CSS / JS Frontend
●
●
●
D2: Testing
●
●
D2: Documentation
●
D3: Hosting and Deployment
●
●
D3: User Manual
●
●
D3: Presentation
●

## 1.6  Conclusion

South Africa's overall digital economy is growing at a rate. The country's online retail
sector is predicted to surpass R130 billion in 2025, accounting for almost 10% of the
retail spending (World Wide Worx and Mastercard, 2025). But the informal sector that
employs almost 20% of the working population and accounts for a township economy
that is estimated to value R900 billion a year (Standard Bank, 2025) has been not been a
meaningful part of that growth. The tools that do exist exploit the workers that use them,
or provide no protection whatsoever. The disconnect between informal, untracked work


---
<!-- Page 14 -->

ITECA3-B12 – Project – Deliverable 1
Page 14 of 15
available in service and a fair and safe digital marketplace has been documented well
and has not been bridged.
HustleHub is a direct and practical solution to that gap. By narrowing down this to
getting physical household services, and having a fixed-price listing model, the platform
remains simple enough to use and simple enough to build. The escrow system is not a
complex financial instrument: It is a status field in a transactions table that is part of a
PHP logic system. The rating system is conventional CRUD. The dispute module is a
notification, a review page and a fund control action for the admin. None of this requires
technology outside of what is specified in the module and none of it requires more than
one developer to finish within the project timeframe.
The potential impact of a platform like this extends beyond the individual user. If
HustleHub provides even a number of township service workers with a profile, a growing
rating and a guaranteed payment on each confirmed job, it helps to move informal work
from something invisible and unprotected into something documented and reliable.
Nguimkeu (2025) describes this as the most important contribution digital platforms
can make to labour markets in developing economies: turning work that is survivalist
and invisible, into work that is formal, verifiable, and capable of growing. At a national
level platform that ensure transaction revenue remains in South Africa, financial
inclusion for workers who do not have access to a bank account, and bringing informal
trade into the formal system is consistent with the economic development objectives
outlined by Standard Bank (2025). HustleHub is one step in that direction, and a step
that is achievable within this project.
The proposal has outlined the problem, the evidence to support it, five measurable
goals, a realistic scope, and a workable timetable. Deliverable 2 will transform this to
prototypes, diagrams and working code. Deliverable 3 will deliver a fully hosted platform
with a documented user journey. This document is the basis of both.
Sources for Section 1.6 World Wide Worx, Mastercard (2025) Standard Bank (2025)
Statistics South Africa (2025) Nguimkeu (2025) Rest of World (2024) BusinessTech
(2025)


---
<!-- Page 15 -->

ITECA3-B12 – Project – Deliverable 1
Page 15 of 15

## Resources

BusinessTech (2024). Huge problem for domestic workers in South Africa. BusinessTech.
Available at: https://businesstech.co.za/news/lifestyle/790504/huge-problem-for-domestic-
workers-in-south-africa/ [Accessed: 24 February 2026].
BusinessTech (2025). How much you must pay your domestic worker in 2025.
BusinessTech. Available at: https://businesstech.co.za/news/finance/811758/how-much-you-
must-pay-your-domestic-worker-in-2025/ [Accessed: 24 February 2026].
Makhitha, K.M. and Ngobeni, K. (2023). Factors influencing the online clothing shopping
intention of emerging township consumers in South Africa: The mediation effect of attitude.
Global Media Journal, 21(62). Available at: https://www.globalmediajournal.com/open-
access/factors-influencing-the-online-clothing-shopping-intention-of-emerging-township-
consumers-in-south-africa-the-mediation-effect-of-.php?aid=92394 [Accessed: 24 February
2026].
Nguimkeu, P. (2025). Africa's growing gig economy: What is needed for success. Brookings
Institution. Available at: https://www.brookings.edu/articles/africas-growing-gig-economy-
what-is-needed-for-success/ [Accessed: 24 February 2026].
Rest of World (2024). Domestic workers in South Africa say they're forced to work extra
hours for free. Rest of World, 29 May 2024. Available at:
https://restofworld.org/2024/sweepsouth-unpaid-work-south-africa/ [Accessed: 24 February
2026].
Standard Bank (2025). Township Informal Economy Report, October 2025. Standard Bank.
Available at:
https://www.standardbank.co.za/staticfile/South%20Africa/PDF/Township/StandardBankTow
nshipInformalEconomyReportOctober2025.pdf [Accessed: 24 February 2026].
Statistics South Africa (2025). Quarterly Labour Force Survey (QLFS): Q1 2025. Stats SA.
Available at: https://www.statssa.gov.za/?page_id=1854&PPN=P0211 [Accessed: 24
February 2026].
SweepSouth (2024). Seventh Annual Domestic Workers Report on Pay and Working
Conditions for Domestic Workers in South Africa. SweepSouth. Available at:
https://sweepsouth.com/blog/7th-annual-domestic-workers-report-on-pay-and-working-
conditions-for-domestic-workers-in-south-africa/ [Accessed: 24 February 2026].
World Wide Worx and Mastercard (2025). South Africa's online retail set to surpass R130
billion in 2025. Mastercard Newsroom. Available at:
https://www.mastercard.com/news/eemea/en/newsroom/press-releases/en/2025-
1/september/south-africa-s-online-retail-set-to-surpass-r130-billion-in-2025/ [Accessed: 24
February 2026].