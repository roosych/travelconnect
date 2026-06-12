Design the initial system architecture for a B2B inbound tourism RFQ platform.

Context:
We are building a platform where:
- Agencies submit travel requests
- Operator converts requests into RFQs
- Suppliers respond with (possibly partial) offers
- Operator assembles proposals from multiple suppliers
- Agency approves proposal → booking is created

Task:
1. Define core domain modules (MVP only)
2. Define main entities and relationships
3. Define RFQ → Offer → Proposal → Booking lifecycle
4. Identify where operator decisions are required
5. Explicitly define boundaries between modules
6. Keep system monolithic (no microservices)
7. Mark anything that is NOT MVP as [POST-MVP]

Output must include:
- architecture overview
- entity map
- lifecycle/state flow
- key decision points for operator