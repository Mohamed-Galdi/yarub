# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Arabic-speaking students learn through purchased courses/lessons and the existing القدرات and مهارات اللغة العربية training programs. Administrators manage content and student accounts in a separate dashboard.

## Product Purpose

YARUB is an existing Arabic learning platform. Preserve its learning programs, payments, subscriptions, progress and authentication. ARRIB is a separate project.

## Capabilities and Constraints

Laravel 10 monolith with Blade, Tailwind, Alpine and Livewire/PowerGrid; no additional frontend framework. Video content is supplementary, with one existing category per video and no progress records or subscription gate. The full library is for authenticated students; guests may access only one explicitly selected eligible example. Local private storage and protected media delivery are implemented. Development only; production prerequisites remain unverified.

## Brand Commitments

Preserve Yarub's Arabic typography, RTL behavior and established Qudrat visual quality, as required by YARUB_VIDEO_LIBRARY_IMPLEMENTATION.md. Extend existing admin navigation and components; no redesign of other surfaces.

## Evidence on Hand

Confirmed user contract: YARUB_VIDEO_LIBRARY_IMPLEMENTATION.md. Repository discovery and implementation records: docs/VIDEO_LIBRARY.md and docs/VIDEO_LIBRARY_MEDIA.md. Existing Qudrat student/admin/public views and resources/css/qudrat.css are the visual reference. No invented counts, claims or content.

## Product Principles

- Keep student and guest access distinct, including files and covers.
- Make publishing and processing states accurate and actionable.
- Preserve current program behavior and unrelated work.
- Complete only the explicitly requested phase and verify it in isolated development.

## Accessibility & Inclusion

Arabic RTL interfaces, readable mixed-direction content, keyboard navigation, visible focus, mobile layouts and reduced-motion support are required by the implementation contract.
