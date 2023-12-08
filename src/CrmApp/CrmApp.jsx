import React, { useEffect } from "react";
import { FormGroup, FormControl, Row, Col } from "react-bootstrap";
import PendingButton from "./PendingButton.jsx";
import CompaniesList from "./CompaniesList.jsx";
import ProvinceLIs from "./ProvinceLIs.jsx";
import { post, put } from "../utils/services.js";
import "./CrmApp.scss";
import "bootstrap/dist/css/bootstrap.min.css";

let searchTimeout;

const noActiveCompany = {
  id: null,
  url: "",
  phoneNumber: "",
  emailAddress: "",
  firstName: "",
  lastName: "",
  streetAddress: "",
  city: "",
  postalCode: "",
  province: "AB",
  isCandidate: 0,
  isTemplate: 0,
  isInactive: 0,
  dontEmail: 0,
  notes: "",
};

export default function CrmApp(props) {
  const [companies, setCompanies] = React.useState([]);
  const [company, setCompany] = React.useState(noActiveCompany);
  const [searchProvince, setSearchProvince] = React.useState("AB");
  const [searchString, setSearchString] = React.useState("");
  const [isSaving, setIsSaving] = React.useState("");
  const [isCreating, setIsCreating] = React.useState(false);

  useEffect(() => {
    document.title = "Sales CRM";
  }, []);

  useEffect(() => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      getCompanies();
    }, 500);
  }, [searchProvince, searchString]);

  function createNewCompany(e) {
    setCompany(noActiveCompany);
    setIsCreating(true);
  }

  function loadCompany(e) {
    setCompany(companies[e.target.id]);
    setIsCreating(false);
  }

  async function createCompany() {
    setIsSaving(true);

    post("api/companies.php", company).then((response) => {
      if (response === "duplicated") {
        alert("URL already exists in the database.");
        setIsSaving(false);
      } else {
        const updated_list = companies.map((com) =>
          com.id === company.id ? company : com
        );
        updated_list.push(company);
        setCompanies(updated_list);
        setIsSaving(false);
        setIsCreating(false);
        const element = document.getElementsByClassName("active");
        if (element.length) {
          element[0].classList.remove("active");
        }
      }
    });
  }

  async function saveCompany() {
    setIsSaving(true);

    put("api/companies.php", company).then(() => {
      setIsSaving(false);
    });
  }

  function handleChange(e) {
    const name = e.target.name;
    let value = e.target.value;
    if (
      name === "isCandidate" ||
      name === "isTemplate" ||
      name === "isInactive" ||
      name === "dontEmail"
    ) {
      value = e.target.checked ? 1 : 0;
    }
    setCompany({ ...company, [name]: value });
  }

  async function getCompanies() {
    setCompany(noActiveCompany);

    const fetch_url = searchString
      ? `api/companies.php?searchString=${searchString}`
      : `api/companies.php?searchProvince=${searchProvince}`;

    await fetch(fetch_url, {
      method: "GET",
    })
      .then((response) => response.json())
      .then((data) => {
        setCompanies(data);
      });
  }

  return (
    <Row>
      <Col sm={3}>
        <FormGroup>
          <FormControl
            type="text"
            placeholder="Search..."
            value={searchString}
            onChange={(e) => {
              setSearchString(e.target.value);
            }}
          />
        </FormGroup>
        <FormGroup>
          <select
            className="form-control"
            value={searchProvince}
            onChange={(e) => {
              setCompanies([]);
              setSearchProvince(e.target.value);
            }}
          >
            <ProvinceLIs />
          </select>
        </FormGroup>
        <CompaniesList
          company={company}
          companies={companies}
          createNewCompany={createNewCompany}
          loadCompany={loadCompany}
        />
      </Col>
      <Col sm={9}>
        <FormGroup>
          <Row>
            <Col sm={2}>URL:</Col>
            <Col sm={9}>
              {!isCreating ? (
                <a href={`http://${company.url}`} target="_blank">
                  {company.url}
                </a>
              ) : (
                <FormControl
                  placeholder="Company URL"
                  name="url"
                  value={company.url}
                  onChange={handleChange}
                />
              )}
            </Col>
          </Row>
        </FormGroup>
        <FormGroup>
          <Row>
            <Col sm={2}>Contact Info:</Col>
            <Col sm={4}>
              <FormControl
                placeholder="Phone number"
                name="phoneNumber"
                value={company.phoneNumber}
                onChange={handleChange}
              />
            </Col>
            <Col sm={5}>
              <FormControl
                placeholder="Email address"
                type="email"
                name="emailAddress"
                value={company.emailAddress}
                onChange={handleChange}
              />
            </Col>
          </Row>
        </FormGroup>
        <FormGroup>
          <Row>
            <Col sm={2}>Contact Name:</Col>
            <Col sm={4}>
              <FormControl
                placeholder="First name"
                name="firstName"
                value={company.firstName}
                onChange={handleChange}
              />
            </Col>
            <Col sm={5}>
              <FormControl
                placeholder="Last name"
                name="lastName"
                value={company.lastName}
                onChange={handleChange}
              />
            </Col>
          </Row>
        </FormGroup>
        <FormGroup>
          <Row>
            <Col sm={2}>Address:</Col>
            <Col sm={4}>
              <FormControl
                placeholder="Street address"
                name="streetAddress"
                value={company.streetAddress}
                onChange={handleChange}
              />
            </Col>
            <Col sm={5}>
              <FormControl
                placeholder="City"
                name="city"
                value={company.city}
                onChange={handleChange}
              />
            </Col>
          </Row>
        </FormGroup>
        <FormGroup>
          <Row>
            <Col sm={2}>Postal Code:</Col>
            <Col sm={4}>
              <FormControl
                placeholder="Postal code"
                name="postalCode"
                value={company.postalCode}
                onChange={handleChange}
              />
            </Col>
            <Col sm={5}>
              <select
                value={company.province}
                name="province"
                onChange={handleChange}
              >
                <ProvinceLIs />
              </select>
            </Col>
          </Row>
        </FormGroup>
        <FormGroup>
          <Row>
            <Col sm={2}>Is Candidate:</Col>
            <Col sm={4}>
              <input
                type="checkbox"
                name="isCandidate"
                value={company.isCandidate}
                checked={parseInt(company.isCandidate)}
                onChange={handleChange}
              />
            </Col>
            <Col sm={2}>Is Template:</Col>
            <Col sm={4}>
              <input
                type="checkbox"
                name="isTemplate"
                value={company.isTemplate}
                checked={parseInt(company.isTemplate)}
                onChange={handleChange}
              />
            </Col>
          </Row>
        </FormGroup>
        <FormGroup>
          <Row>
            <Col sm={2}>Is Inactive:</Col>
            <Col sm={4}>
              <input
                type="checkbox"
                name="isInactive"
                value={company.isInactive}
                checked={parseInt(company.isInactive)}
                onChange={handleChange}
              />
            </Col>
            <Col sm={2}>Dont Email:</Col>
            <Col sm={4}>
              <input
                type="checkbox"
                name="dontEmail"
                value={company.dontEmail}
                checked={parseInt(company.dontEmail)}
                onChange={handleChange}
              />
            </Col>
          </Row>
        </FormGroup>

        <FormGroup>
          <Row>
            <Col sm={2}>Notes:</Col>
            <Col sm={9}>
              <textarea
                className="notes-textarea form-control"
                name="notes"
                value={company.notes || ""}
                onChange={handleChange}
              />
            </Col>
          </Row>
        </FormGroup>
        <PendingButton
          disabled={isSaving}
          onClick={isCreating ? createCompany : saveCompany}
          pending={isSaving}
          saving={isSaving}
          text={isCreating ? "Create" : "Save"}
          pendingText={isCreating ? "Creating..." : "Saving..."}
        />
      </Col>
    </Row>
  );
}
