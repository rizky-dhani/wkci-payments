# Transaction History Import Guide

To import transaction history data via Excel, please follow the format outlined below:

## Required Columns

Your Excel file should contain the following columns:

| Column Header | Data Type | Required | Description |
|---------------|-----------|----------|-------------|
| transaction_date | Date | Yes | Date of the transaction in YYYY-MM-DD format |
| amount | Number | Yes | Transaction amount |
| transaction_time | Time | No | Time of the transaction in HH:MM format |
| remarks | Text | No | Additional notes about the transaction (max 1000 characters) |

## Sample Data

| transaction_date | transaction_time | amount | remarks |
|------------------|------------------|--------|---------|
| 2024-01-15 | 09:30 | 100000 | Payment for service |
| 2024-01-16 | 14:25 | 150000 | Product purchase |
| 2024-01-17 | | 75000 | Service fee |

## Important Notes

- The first row should contain the column headers (as shown above)
- All dates should be in YYYY-MM-DD format
- Amount values should be numeric without currency symbols
- Time values should be in 24-hour format (HH:MM)
- The system will automatically generate appropriate transaction numbers
- Supported file formats: .xlsx, .xls, .csv

## Import Process

1. Click on "Import" button in the Transaction History management page
2. Upload your Excel file
3. Map your file columns to the appropriate fields in the system
4. Click "Import" to start the import process
5. You'll receive a notification upon successful import or if any errors occur