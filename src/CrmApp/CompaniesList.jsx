import React from "react";

export default function CompaniesList({ company, companies, createNewCompany, loadCompany }) {
  if (!companies.length) {
    return (
      <div className="list-companies">
        <div className="list-company no-hover">No matches</div>
        <div key="new" id="new" onClick={createNewCompany} className="list-company">
          Create New Company
        </div>
      </div>
    );
  }

  return (
    <div className="list-companies">
      <div key="new" onClick={createNewCompany} className="list-company">
        Create New Company
      </div>
      {companies.map((_company, index) => {
        let className = `list-company`;
        if (_company.id === company.id) {
          className += ` active `;
        }
        if (parseInt(_company.isInactive) || parseInt(_company.dontEmail) || parseInt(_company.isTemplate)) {
          className += ` text-red `;
        }
        return (
          <div key={index} id={index} onClick={loadCompany} className={className}>
            {_company.url}
          </div>
        );
      })}
    </div>
  );
}
