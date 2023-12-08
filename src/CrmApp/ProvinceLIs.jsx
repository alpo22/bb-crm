import React from "react";
import { provinces, states } from "../utils/library.js";

export default function ProvinceLIs() {
  const provincesHtml = [];

  provincesHtml.push(
    <option value="all" key="all">
      ALL
    </option>
  );

  provincesHtml.push(
    provinces.map((province, index) => (
      <option value={province.value} key={index}>
        {province.text}
      </option>
    ))
  );
  provincesHtml.push(
    <option value="--" key="divider">
      --
    </option>
  );
  provincesHtml.push(
    states.map((state, index) => (
      <option value={state.value} key={index}>
        {state.text}
      </option>
    ))
  );
  provincesHtml.push(
    <option value="--" key="divider2">
      --
    </option>
  );
  provincesHtml.push(
    <option value="AU" key="au">
      Australia
    </option>
  );
  provincesHtml.push(
    <option value="UK" key="uk">
      UK
    </option>
  );
  provincesHtml.push(
    <option value="??" key="other">
      Other
    </option>
  );

  return provincesHtml;
}
