# TimeCamp Sync for PerfexCRM

**TimeCamp Sync** is a PerfexCRM module that integrates with TimeCamp to import time entries directly into Perfex projects.  
It automatically maps projects by name, fetches recent entries, and logs them as task time logs and Perfex-native timers.

## Features
- Maps PerfexCRM Projects to TimeCamp Tasks
- Imports time entries as task comments and `tbltaskstimers` records
- Creates internal logs (`tbl_timecamp_logs`) for auditing
- Sync button injected automatically on Project > Timesheets tab
- Respects staff hourly rates pulled from Perfex staff profile

## Installation
1. Upload the module folder to `/modules/timecamp_sync/`
2. Activate it from **Setup > Modules**
3. Visit the **TimeCamp Sync Settings** in the sidebar and add your API key
4. Use the "Sync from TimeCamp" button in any project’s timesheet tab

## Requirements
- PerfexCRM 2.3+
- TimeCamp API Key (OAuth or personal token)
- Matching project names between Perfex and TimeCamp
- Must have at least one task inside the project

## License
This module is published under the MIT License.  
Please see the [LICENSE](LICENSE) file for full details.

---

Developed and maintained by **[ÖMER TEKNOLOJİ](https://omertek.com)**
