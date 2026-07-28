# User Roles System — Proposal

## Problem
Current authorization uses a binary `is_admin` boolean. No granularity for different access levels.

## Solution
Replace `is_admin` with a role-based system: `admin`, `editor`, `viewer`.

## Key Decisions
- String-based `role` column (flexible, no enum migration issues)
- Backward compat: keep `is_admin` as derived attribute until Phase 5 cleanup
- Custom middleware (`CheckRole`) with parameterized role levels
- No external packages (Spatie Permission explicitly excluded)
