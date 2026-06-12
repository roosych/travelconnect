---
name: Offer withdrawn status addition
description: The architecture did not include a withdrawn status for offers; the workflow design added it
type: project
---

The architecture.md defines offer statuses as: received, reviewed, accepted, rejected, expired. The workflow design identified that `withdrawn` (supplier retracts an offer) is a distinct business event from operator `rejected` and must be a separate status value.

This was added in workflow.md section 4.1 and handled as edge case 13.3.

**Why:** A supplier withdrawal after proposal assembly is a critical operational event with different downstream implications than an operator-initiated rejection. Conflating them loses audit trail fidelity.
**How to apply:** When implementing the offers table migration, add `withdrawn` to the status enum. Also update architecture.md to reflect this addition.
